<?php
namespace AppBundle\Controller;

use AppBundle\Entity\AttendanceAssignment;
use AppBundle\Entity\KfcAttendance;
use AppBundle\Entity\KfcAttendanceDetail;
use Proxies\__CG__\AppBundle\Entity\AttendanceProfile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @Route("/kfc-attendance")
 */

class KfcAttendanceController extends Controller
{
    /**
     * @Route("", name="kfc_attendance_index", options={"main" = true })
     */

    public function kfcAttendanceAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');
        $imgUrl = $this->getParameter('img_url');
        $reportUrl = $this->getParameter('report_url');

        return $this->render(
            'template/kfc-attendance/index.html.twig',
            [
                'user' => $user,
                'hostIp' => $hostIp,
                'imgUrl' => $imgUrl,
                'reportUrl' => $reportUrl
            ]
        );
    }

    /**
     * @Route("/ajax_get_datatable_kfc_attendance", 
     * name="ajax_get_datatable_kfc_attendance", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxGetDatatableKfcAttendanceAction(Request $request)
    {
        $columns = array(
            0 => "h.id",
            1 => "h.description",
            2 => "h.municipality_name",
            3 => "h.barangay_name",
            4 => "h.voter_group",
            5 => "h.position",
            6 => "h.meeting_date"
        );

        $sWhere = "";

        $select['h.description'] = $request->get('description');
        $select['h.municipality_name'] = $request->get('municipalityName');
        $select['h.barangay_name'] = $request->get('barangayName');
        $select['h.meeting_date'] = $request->get('meetingDate');
        $select['h.meeting_group'] = $request->get('meetingGroup');
        $select['h.meeting_position'] = $request->get('meetingPosition');

        foreach ($select as $key => $value) {
            $searchValue = $select[$key];
            if ($searchValue != null || !empty($searchValue)) {
                $sWhere .= " AND " . $key . " LIKE \"%" . $searchValue . "%\"";
            }
        }

        $sOrder = "";

        if (null !== $request->query->get('order')) {
            $sOrder = "ORDER BY  ";
            for ($i = 0; $i < intval(count($request->query->get('order'))); $i++) {
                if ($request->query->get('columns')[$request->query->get('order')[$i]['column']]['orderable']) {
                    $selected_column = $columns[$request->query->get('order')[$i]['column']];
                    $sOrder .= " " . $selected_column . " " .
                        ($request->query->get('order')[$i]['dir'] === 'asc' ? 'ASC' : 'DESC') . ", ";
                }
            }

            $sOrder = substr_replace($sOrder, "", -2);
            if ($sOrder == "ORDER BY") {
                $sOrder = "";
            }
        }

        $start = 1;
        $length = 1;

        if (null !== $request->query->get('start') && null !== $request->query->get('length')) {
            $start = intval($request->query->get('start'));
            $length = intval($request->query->get('length'));
        }

        $em = $this->getDoctrine()->getManager("electPrep2024");
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(h.id),0) FROM tbl_attendance h WHERE 1 ";

        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.id),0) FROM tbl_attendance h WHERE 1 ";

        $sql .= $sWhere . " " . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT h.*,
                (SELECT COUNT(*) FROM tbl_attendance_detail ad WHERE ad.hdr_id = h.id) AS total_attendee ,
                (SELECT COUNT(*) FROM tbl_attendance_detail ad WHERE ad.hdr_id = h.id AND ad.has_profile = 1) AS total_attendee_profile,
                (SELECT COUNT(*) FROM tbl_attendance_detail ad WHERE ad.hdr_id = h.id AND ad.has_assignment = 1) AS total_attendee_assignment 
                FROM tbl_attendance h 
                WHERE 1 " . $sWhere . "  ORDER BY h.meeting_date  LIMIT {$length} OFFSET {$start} ";

        $stmt = $em->getConnection()->query($sql);
        $data = [];


        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        $draw = (null !== $request->query->get('draw')) ? $request->query->get('draw') : 0;
        $res['data'] = $data;
        $res['recordsTotal'] = $recordsTotal;
        $res['recordsFiltered'] = $recordsFiltered;
        $res['draw'] = $draw;

        return new JsonResponse($res);
    }

    /**
     * @Route("/ajax_post_kfc_attendance", 
     * 	name="ajax_post_kfc_attendance",
     *	options={"expose" = true}
     * )
     * @Method("POST")
     */

    public function ajaxPostKfcAttendanceAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

       $em = $this->getDoctrine()->getManager("electPrep2024");

        $entity = new KfcAttendance();

        $entity->setMunicipalityNo($request->get('municipalityNo'));
        $entity->setBarangayNo($request->get('barangayNo'));
        $entity->setDescription($request->get('description'));
        $entity->setMeetingDate($request->get('meetingDate'));
        $entity->setMeetingPosition($request->get('meetingPosition'));
        $entity->setMeetingGroup($request->get('meetingGroup'));
        $entity->setStatus("A");
        $entity->setCreatedAt(new \DateTime());
        $entity->setCreatedBy($user->getUsername());

        $validator = $this->get('validator');
        $violations = $validator->validate($entity);

        $errors = [];

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse($errors, 400);
        }


        $sql = "SELECT *  FROM psw_municipality WHERE province_code = 53 AND municipality_no = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $request->get('municipalityNo'));
        $stmt->execute();

        $municipality = $stmt->fetch(\PDO::FETCH_ASSOC);


        $sql = "SELECT *  FROM psw_barangay WHERE municipality_code = ? AND brgy_no = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipality['municipality_code']);
        $stmt->bindValue(2, $request->get('barangayNo'));
        $stmt->execute();

        $barangay = $stmt->fetch(\PDO::FETCH_ASSOC);

        $entity->setMunicipalityName($municipality['name']);
        $entity->setBarangayName($barangay['name']);

        $em->persist($entity);
        $em->flush();

        $em->clear();
        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($entity));
    }


    /**
     * @Route("/ajax_post_kfc_attendance_detail/{id}", 
     * 	name="ajax_post_kfc_attendance_detail",
     *	options={"expose" = true}
     * )
     * @Method("POST")
     */

    public function ajaxPostKfcAttendanceDetailAction(Request $request, $id)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

       $em = $this->getDoctrine()->getManager("electPrep2024");


        $attendance = $em->getRepository("AppBundle:KfcAttendance")->find($id);

        if (!$attendance) {
            return new JsonResponse([], 404);
        }

        $voter = $em->getRepository("AppBundle:ProjectVoter")->find($request->get('proVoterId'));

        if (!$voter) {
            return new JsonResponse([], 404);
        }

        $entity = new KfcAttendanceDetail();

        $entity->setHdrId($attendance->getId());
        $entity->setProVoterId($voter->getProVoterId());
        $entity->setProIdCode($voter->getProIdCode());
        $entity->setGeneratedIdNo($voter->getGeneratedIdNo());
        $entity->setVoterName($voter->getVoterName());

        $entity->setMunicipalityName($voter->getMunicipalityName());
        $entity->setBarangayName($voter->getBarangayName());
        $entity->setIsNonVoter($voter->getIsNonVoter());

        $contactNo = empty($request->get("contactNo")) ? $voter->getCellphone() : $request->get('contactNo');

        $entity->setContactNo($contactNo);
        $entity->setHasAssignment(0);
        $entity->setHasProfile(0);

        $entity->setCreatedAt(new \DateTime());
        $entity->setCreatedBy($user->getUsername());
        $entity->setStatus("A");

        $validator = $this->get('validator');
        $violations = $validator->validate($entity);

        $errors = [];

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse($errors, 400);
        }

        $em->persist($entity);
        $em->flush();

        $voter->setCellphone($entity->getContactNo());
        $em->flush();

        $em->clear();
        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($entity));
    }

    /**
     * @Route("/ajax_get_datatable_kfc_attendance_detail", 
     * name="ajax_get_datatable_kfc_attendance_detail", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxGetDatatableKfcAttendanceDetailAction(Request $request)
    {
        $columns = array(
            0 => "h.id",
            1 => "h.voter_name",
            2 => "h.municipality_name",
            3 => "h.barangay_name",
            4 => "h.contact_no"
        );

        $sWhere = "";

        $select['h.voter_name'] = $request->get('voterName');
        $select['h.municipality_name'] = $request->get('municipalityName');
        $select['h.barangay_name'] = $request->get('barangayName');
        $select['h.contact_no'] = $request->get('contactNo');
        $select['h.hdr_id'] = $request->get('hdrId');
        
        foreach ($select as $key => $value) {
            $searchValue = $select[$key];
            if ($searchValue != null || !empty($searchValue)) {
                if($key == 'h.hdr_id'){
                    $sWhere .= " AND " . $key . "= '" . $searchValue . "' ";
                }else{
                    $sWhere .= " AND " . $key . " LIKE \"%" . $searchValue . "%\"";
                }
            }
        }

        $sOrder = "";

        if (null !== $request->query->get('order')) {
            $sOrder = "ORDER BY  ";
            for ($i = 0; $i < intval(count($request->query->get('order'))); $i++) {
                if ($request->query->get('columns')[$request->query->get('order')[$i]['column']]['orderable']) {
                    $selected_column = $columns[$request->query->get('order')[$i]['column']];
                    $sOrder .= " " . $selected_column . " " .
                        ($request->query->get('order')[$i]['dir'] === 'asc' ? 'ASC' : 'DESC') . ", ";
                }
            }

            $sOrder = substr_replace($sOrder, "", -2);
            if ($sOrder == "ORDER BY") {
                $sOrder = "";
            }
        }

        $start = 1;
        $length = 1;

        if (null !== $request->query->get('start') && null !== $request->query->get('length')) {
            $start = intval($request->query->get('start'));
            $length = intval($request->query->get('length'));
        }

       $em = $this->getDoctrine()->getManager("electPrep2024");
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(h.id),0) FROM tbl_attendance_detail h WHERE 1 ";

        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.id),0) FROM tbl_attendance_detail h WHERE 1 ";

        $sql .= $sWhere . " " . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        
        $sql = "SELECT h.*,
                (SELECT COALESCE(COUNT(*),0) FROM tbl_attendance_assignment a WHERE a.hdr_id = h.id) AS total_assignment,
                (SELECT COALESCE(COUNT(*),0) FROM tbl_attendance_profile p WHERE p.hdr_id = h.id) AS total_profile 
                FROM tbl_attendance_detail h 
                WHERE 1 " . $sWhere . " " . $sOrder . " LIMIT {$length} OFFSET {$start} ";

        $stmt = $em->getConnection()->query($sql);
        $data = [];


        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        $draw = (null !== $request->query->get('draw')) ? $request->query->get('draw') : 0;
        $res['data'] = $data;
        $res['recordsTotal'] = $recordsTotal;
        $res['recordsFiltered'] = $recordsFiltered;
        $res['draw'] = $draw;

        return new JsonResponse($res);
    }


    /**
     * @Route("/ajax_patch_project_voter_tag_status/{proVoterId}", 
     * 	name="ajax_patch_project_voter_tag_status",
     *	options={"expose" = true}
     * )
     * @Method("PATCH")
     */

    public function patchProjectVoterTagStatus($proVoterId, Request $request)
    {
       $em = $this->getDoctrine()->getManager("electPrep2024");
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $projectVoter = $em->getRepository("AppBundle:ProjectVoter")->find($proVoterId);

        if (!$projectVoter)
            return new JsonResponse(null, 404);


        if ($this->isTogglable($request->get("isKalaban"))) {
            $projectVoter->setIsKalaban($request->get('isKalaban'));
        }

        $projectVoter->setDidChanged(1);
        $projectVoter->setUpdatedAt(new \DateTime());
        $projectVoter->setUpdatedBy($user->getUsername());

        $em->flush();
        $em->clear();

        return new JsonResponse([
            "success" => true
        ]);
    }

    /**
     * @Route("/ajax_patch_attendance_tag_has_profile/{id}", 
     * 	name="ajax_patch_attendance_tag_has_profile",
     *	options={"expose" = true}
     * )
     * @Method("PATCH")
     */

    public function patchAttendanceTagHasProfile($id, Request $request)
    {

        $em = $this->getDoctrine()->getManager("electPrep2024");
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $entity = $em->getRepository("AppBundle:KfcAttendanceDetail")->find($id);

        if (!$entity)
            return new JsonResponse(null, 404);

        if ($this->isTogglable($request->get("hasProfile"))) {
            $entity->setHasProfile($request->get("hasProfile"));
        }

        if ($this->isTogglable($request->get("hasAssignment"))) {
            $entity->setHasAssignment($request->get("hasAssignment"));
        }

        $em->flush();
        $em->clear();

        return new JsonResponse([
            "success" => true
        ]);
    }

    private function isTogglable($value)
    {
        return $value != null && $value != "" && ($value == 0 || $value == 1);
    }

    /**
     * @Route("/ajax_delete_kfc_attendance_detail/{id}", 
     * 	name="ajax_delete_kfc_attendance_detail",
     *	options={"expose" = true}
     * )
     * @Method("DELETE")
     */

    public function ajaxDeleteKfcAttendanceDetailAction($id)
    {
       $em = $this->getDoctrine()->getManager("electPrep2024");
        $entity = $em->getRepository("AppBundle:KfcAttendanceDetail")->find($id);

        if (!$entity)
            return new JsonResponse(null, 404);

        //remove assignments

        $assignments = $em->getRepository("AppBundle:AttendanceAssignment")->findBy([
            'hdrId' => $id
        ]);

        foreach($assignments as $assignment) {
            $em->remove($assignment);
        }

        //remove profiles

        
        $profiles = $em->getRepository("AppBundle:AttendanceProfile")->findBy([
            'hdrId' => $id
        ]);

        foreach($profiles as $profile) {
            $em->remove($profile);
        }

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null, 200);
    }

    /**
     * @Route("/ajax_delete_kfc_attendance/{id}", 
     * 	name="ajax_delete_kfc_attendance",
     *	options={"expose" = true}
     * )
     * @Method("DELETE")
     */

    public function ajaxDeleteKfcAttendanceAction($id)
    {
       $em = $this->getDoctrine()->getManager("electPrep2024");
        $entity = $em->getRepository("AppBundle:KfcAttendance")->find($id);

        if (!$entity)
            return new JsonResponse(null, 404);

        $entities = $em->getRepository("AppBundle:KfcAttendanceDetail")->findBy([
            'hdrId' => $id
        ]);

        foreach($entities as $entity) {
            $em->remove($entity);
        }

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null, 200);
    }

    /**
     * @Route("/ajax_kfc_get_transfer_assignments",
     *       name="ajax_kfc_get_transfer_assignments",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

     public function ajaxKfcTransferAssignments(Request $request)
     {
         $em = $this->getDoctrine()->getManager("electPrep2024");
    
         $sql = "SELECT h.*, ad.id AS detail_id FROM tbl_attendance_detail ad INNER JOIN tbl_recruitment_hdr h 
                 ON ad.pro_voter_id = h.pro_voter_id 
                 WHERE h.voter_group = 'Household Leader' ";
 
         $stmt = $em->getConnection()->prepare($sql);
         $stmt->execute();

         $hdrs = [];
        
         while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $hdrs[] = $row;
         }
         
         foreach($hdrs as $hdr){
            $sql = "SELECT * FROM tbl_recruitment_dtl d WHERE rec_id = ?";
            
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $hdr['id']);
            $stmt->execute();

            $details = [];
            $details = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach($details as $detail){
                $entity = new AttendanceProfile();
                $entity->setHdrId($hdr['detail_id']);
                $entity->setProVoterId($detail['pro_voter_id']);
                $entity->setProIdCode($detail['pro_id_code']);
                //$entity->setGeneratedIdNo($detail['generated_id_no']);
                $entity->setVoterName($detail['voter_name']);
                $entity->setMunicipalityName($detail['municipality_name']);
                $entity->setBarangayName($detail['barangay_name']);
                $entity->setStatus('A');

                $em->persist($entity);
                $em->flush();
            }
         }

         return new JsonResponse([],200);
    }
 

    /**
     * @Route("/ajax_get_datatable_kfc_attendance_assignment", 
     * name="ajax_get_datatable_kfc_attendance_assignment", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxGetDatatableKfcAttendanceAssignmentAction(Request $request)
    {
        $columns = array(
            0 => "h.id",
            1 => "h.voter_name",
            2 => "h.municipality_name",
            3 => "h.barangay_name",
            4 => "h.contact_no"
        );

        $sWhere = "";

        $select['h.voter_name'] = $request->get('voterName');
        $select['h.municipality_name'] = $request->get('municipalityName');
        $select['h.barangay_name'] = $request->get('barangayName');
        $select['h.contact_no'] = $request->get('contactNo');
        $select['h.hdr_id'] = $request->get('hdrId');
        
        foreach ($select as $key => $value) {
            $searchValue = $select[$key];
            if ($searchValue != null || !empty($searchValue)) {
                if($key == 'h.hdr_id'){
                    $sWhere .= " AND " . $key . "= '" . $searchValue . "' ";
                }else{
                    $sWhere .= " AND " . $key . " LIKE \"%" . $searchValue . "%\"";
                }
            }
        }

        $sOrder = "";

        if (null !== $request->query->get('order')) {
            $sOrder = "ORDER BY  ";
            for ($i = 0; $i < intval(count($request->query->get('order'))); $i++) {
                if ($request->query->get('columns')[$request->query->get('order')[$i]['column']]['orderable']) {
                    $selected_column = $columns[$request->query->get('order')[$i]['column']];
                    $sOrder .= " " . $selected_column . " " .
                        ($request->query->get('order')[$i]['dir'] === 'asc' ? 'ASC' : 'DESC') . ", ";
                }
            }

            $sOrder = substr_replace($sOrder, "", -2);
            if ($sOrder == "ORDER BY") {
                $sOrder = "";
            }
        }

        $start = 1;
        $length = 1;

        if (null !== $request->query->get('start') && null !== $request->query->get('length')) {
            $start = intval($request->query->get('start'));
            $length = intval($request->query->get('length'));
        }

       $em = $this->getDoctrine()->getManager("electPrep2024");
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(h.id),0) FROM tbl_attendance_assignment h WHERE 1 ";

        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.id),0) FROM tbl_attendance_assignment h WHERE 1 ";

        $sql .= $sWhere . " " . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        
        $sql = "SELECT h.* FROM tbl_attendance_assignment h 
                WHERE 1 " . $sWhere . " " . $sOrder . " LIMIT {$length} OFFSET {$start} ";

        $stmt = $em->getConnection()->query($sql);
        $data = [];


        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        $draw = (null !== $request->query->get('draw')) ? $request->query->get('draw') : 0;
        $res['data'] = $data;
        $res['recordsTotal'] = $recordsTotal;
        $res['recordsFiltered'] = $recordsFiltered;
        $res['draw'] = $draw;

        return new JsonResponse($res);
    }

    /**
     * @Route("/ajax_post_kfc_attendance_detail_assignment/{dtlId}", 
     * 	name="ajax_post_kfc_attendance_detail_assignment",
     *	options={"expose" = true}
     * )
     * @Method("POST")
     */

     public function ajaxPostKfcAttendanceDetailAssignmentAction(Request $request, $dtlId)
     {
         $user = $this->get('security.token_storage')->getToken()->getUser();
 
        $em = $this->getDoctrine()->getManager("electPrep2024");
 
         $detail = $em->getRepository("AppBundle:KfcAttendanceDetail")->find($dtlId);
 
         if (!$detail) {
             return new JsonResponse([], 404);
         }
 
         $voter = $em->getRepository("AppBundle:ProjectVoter")->find($request->get('proVoterId'));
 
         if (!$voter) {
             return new JsonResponse([], 404);
         }
 
         $entity = new AttendanceAssignment();
 
         $entity->setHdrId($detail->getId());
         $entity->setProVoterId($voter->getProVoterId());
         $entity->setProIdCode($voter->getProIdCode());
         $entity->setGeneratedIdNo($voter->getGeneratedIdNo());
         $entity->setVoterName($voter->getVoterName());
 
         $entity->setMunicipalityName($voter->getMunicipalityName());
         $entity->setBarangayName($voter->getBarangayName());
         $entity->setIsNonVoter($voter->getIsNonVoter());
 
         $contactNo = empty($request->get("contactNo")) ? $voter->getCellphone() : $request->get('contactNo');
 
         $entity->setContactNo($contactNo);
         $entity->setBirthdate($request->get('birthdate'));

         $entity->setCreatedAt(new \DateTime());
         $entity->setCreatedBy($user->getUsername());
         $entity->setStatus("A");
 
         $validator = $this->get('validator');
         $violations = $validator->validate($entity);
 
         $errors = [];
 
         if (count($violations) > 0) {
             foreach ($violations as $violation) {
                 $errors[$violation->getPropertyPath()] = $violation->getMessage();
             }
             return new JsonResponse($errors, 400);
         }
 
         $em->persist($entity);
         $em->flush();
 
         if(!empty($contactNo)){
            $voter->setCellphone($entity->getContactNo());
         }

         if(!empty($request->get('birthdate'))){
            $voter->setBirthdate($entity->getBirthdate());
         }

         $em->flush();
 
         $em->clear();
         $serializer = $this->get('serializer');
 
         return new JsonResponse($serializer->normalize($entity));
     }

     /**
     * @Route("/ajax_delete_kfc_attendance_detail_assignment/{id}", 
     * 	name="ajax_delete_kfc_attendance_detail_assignment",
     *	options={"expose" = true}
     * )
     * @Method("DELETE")
     */

    public function ajaxDeleteKfcAttendanceDetailAssignmentAction($id)
    {
       $em = $this->getDoctrine()->getManager("electPrep2024");
        $entity = $em->getRepository("AppBundle:AttendanceAssignment")->find($id);

        if (!$entity)
            return new JsonResponse(null, 404);

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null, 200);
    }

     /**
     * @Route("/ajax_delete_kfc_attendance_detail_profile/{id}", 
     * 	name="ajax_delete_kfc_attendance_detail_profile",
     *	options={"expose" = true}
     * )
     * @Method("DELETE")
     */

     public function ajaxDeleteKfcAttendanceDetailProfileAction($id)
     {
        $em = $this->getDoctrine()->getManager("electPrep2024");
         $entity = $em->getRepository("AppBundle:AttendanceProfile")->find($id);
 
         if (!$entity)
             return new JsonResponse(null, 404);
 
         $em->remove($entity);
         $em->flush();
 
         return new JsonResponse(null, 200);
     }
 

    
    /**
     * @Route("/ajax_get_datatable_kfc_attendance_profile", 
     * name="ajax_get_datatable_kfc_attendance_profile", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxGetDatatableKfcAttendanceProfileAction(Request $request)
    {
        $columns = array(
            0 => "h.id",
            1 => "h.voter_name",
            2 => "h.municipality_name",
            3 => "h.barangay_name",
            4 => "h.contact_no"
        );

        $sWhere = "";

        $select['h.voter_name'] = $request->get('voterName');
        $select['h.municipality_name'] = $request->get('municipalityName');
        $select['h.barangay_name'] = $request->get('barangayName');
        $select['h.contact_no'] = $request->get('contactNo');
        $select['h.hdr_id'] = $request->get('hdrId');
        
        foreach ($select as $key => $value) {
            $searchValue = $select[$key];
            if ($searchValue != null || !empty($searchValue)) {
                if($key == 'h.hdr_id'){
                    $sWhere .= " AND " . $key . "= '" . $searchValue . "' ";
                }else{
                    $sWhere .= " AND " . $key . " LIKE \"%" . $searchValue . "%\"";
                }
            }
        }

        $sOrder = "";

        if (null !== $request->query->get('order')) {
            $sOrder = "ORDER BY  ";
            for ($i = 0; $i < intval(count($request->query->get('order'))); $i++) {
                if ($request->query->get('columns')[$request->query->get('order')[$i]['column']]['orderable']) {
                    $selected_column = $columns[$request->query->get('order')[$i]['column']];
                    $sOrder .= " " . $selected_column . " " .
                        ($request->query->get('order')[$i]['dir'] === 'asc' ? 'ASC' : 'DESC') . ", ";
                }
            }

            $sOrder = substr_replace($sOrder, "", -2);
            if ($sOrder == "ORDER BY") {
                $sOrder = "";
            }
        }

        $start = 1;
        $length = 1;

        if (null !== $request->query->get('start') && null !== $request->query->get('length')) {
            $start = intval($request->query->get('start'));
            $length = intval($request->query->get('length'));
        }

       $em = $this->getDoctrine()->getManager("electPrep2024");
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(h.id),0) FROM tbl_attendance_profile h WHERE 1 ";

        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.id),0) FROM tbl_attendance_profile h WHERE 1 ";

        $sql .= $sWhere . " " . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        
        $sql = "SELECT h.* FROM tbl_attendance_profile h 
                WHERE 1 " . $sWhere . " " . $sOrder . " LIMIT {$length} OFFSET {$start} ";

        $stmt = $em->getConnection()->query($sql);
        $data = [];


        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        $draw = (null !== $request->query->get('draw')) ? $request->query->get('draw') : 0;
        $res['data'] = $data;
        $res['recordsTotal'] = $recordsTotal;
        $res['recordsFiltered'] = $recordsFiltered;
        $res['draw'] = $draw;

        return new JsonResponse($res);
    }

    /**
     * @Route("/ajax_post_kfc_attendance_detail_profile/{dtlId}", 
     * 	name="ajax_post_kfc_attendance_detail_profile",
     *	options={"expose" = true}
     * )
     * @Method("POST")
     */

     public function ajaxPostKfcAttendanceDetailProfileAction(Request $request, $dtlId)
     {
         $user = $this->get('security.token_storage')->getToken()->getUser();
 
        $em = $this->getDoctrine()->getManager("electPrep2024");
 
 
         $detail = $em->getRepository("AppBundle:KfcAttendanceDetail")->find($dtlId);
 
         if (!$detail) {
             return new JsonResponse([], 404);
         }
 
         $voter = $em->getRepository("AppBundle:ProjectVoter")->find($request->get('proVoterId'));
 
         if (!$voter) {
             return new JsonResponse([], 404);
         }
 
         $entity = new AttendanceProfile();
 
         $entity->setHdrId($detail->getId());
         $entity->setProVoterId($voter->getProVoterId());
         $entity->setProIdCode($voter->getProIdCode());
         $entity->setGeneratedIdNo($voter->getGeneratedIdNo());
         $entity->setVoterName($voter->getVoterName());
 
         $entity->setMunicipalityName($voter->getMunicipalityName());
         $entity->setBarangayName($voter->getBarangayName());
         $entity->setIsNonVoter($voter->getIsNonVoter());
 
         $contactNo = empty($request->get("contactNo")) ? $voter->getCellphone() : $request->get('contactNo');
 
         $entity->setContactNo($contactNo);
         $entity->setBirthdate($request->get('birthdate'));

         $entity->setCreatedAt(new \DateTime());
         $entity->setCreatedBy($user->getUsername());
         $entity->setStatus("A");
 
         $validator = $this->get('validator');
         $violations = $validator->validate($entity);
 
         $errors = [];
 
         if (count($violations) > 0) {
             foreach ($violations as $violation) {
                 $errors[$violation->getPropertyPath()] = $violation->getMessage();
             }
             return new JsonResponse($errors, 400);
         }
 
         $em->persist($entity);
         $em->flush();
 
         if(!empty($contactNo)){
            $voter->setCellphone($entity->getContactNo());
         }

         if(!empty($request->get('birthdate'))){
            $voter->setBirthdate($entity->getBirthdate());
         }

         $em->flush();
 
         $em->clear();
         $serializer = $this->get('serializer');
 
         return new JsonResponse($serializer->normalize($entity));
     }

     /**
     * @Route("/ajax_select2_meeting_position",
     *       name="ajax_select2_meeting_position",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

     public function ajaxSelect2MeetingPosition(Request $request)
     {
         $searchText = trim(strtoupper($request->get('searchText')));
         $searchText = '%' . strtoupper($searchText) . '%';
 
         $em = $this->getDoctrine()->getManager("electPrep2024");
 
         $sql = "SELECT DISTINCT c.meeting_position FROM tbl_attendance c
                 WHERE  (c.meeting_position LIKE ? OR ? IS NULL) AND c.meeting_position IS NOT NULL  ORDER BY c.meeting_position ASC LIMIT 30 ";
 
         $stmt = $em->getConnection()->prepare($sql);
         $stmt->bindValue(1, $searchText);
         $stmt->bindValue(2, ($request->get("searchText") == "") ? null : $request->get("searchText"));
 
         $stmt->execute();
         $data = $stmt->fetchAll();
 
         if (count($data) <= 0) {
             return new JsonResponse(array());
         }
 
         $em->clear();
 
         return new JsonResponse($data);
     }

     /**
     * @Route("/ajax_select2_meeting_group",
     *       name="ajax_select2_meeting_group",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

     public function ajaxSelect2MeetingGroup(Request $request)
     {
         $searchText = trim(strtoupper($request->get('searchText')));
         $searchText = '%' . strtoupper($searchText) . '%';
 
         $em = $this->getDoctrine()->getManager("electPrep2024");
 
         $sql = "SELECT DISTINCT c.meeting_group FROM tbl_attendance c
                 WHERE  (c.meeting_group LIKE ? OR ? IS NULL) AND c.meeting_group IS NOT NULL ORDER BY c.meeting_group ASC LIMIT 30 ";
 
         $stmt = $em->getConnection()->prepare($sql);
         $stmt->bindValue(1, $searchText);
         $stmt->bindValue(2, ($request->get("searchText") == "") ? null : $request->get("searchText"));
 
         $stmt->execute();
         $data = $stmt->fetchAll();
 
         if (count($data) <= 0) {
             return new JsonResponse(array());
         }
 
         $em->clear();
 
         return new JsonResponse($data);
     }
}
