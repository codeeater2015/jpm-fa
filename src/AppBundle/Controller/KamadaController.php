<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use AppBundle\Entity\PrelistingHeader;
use AppBundle\Entity\PrelistingDetail;
use AppBundle\Entity\KamadaHeader;
use AppBundle\Entity\KamadaDetail;

/**
 * @Route("/kamada")
 */

class KamadaController extends Controller
{

    /**
     * @Route("", name="kamada_index", options={"main" = true })
     */

     public function indexAction(Request $request)
     {
         $user = $this->get('security.token_storage')->getToken()->getUser();
         $hostIp = $this->getParameter('host_ip');
         $imgUrl = $this->getParameter('img_url');
 
         return $this->render('template/kamada/index.html.twig', ['user' => $user, "hostIp" => $hostIp, 'imgUrl' => $imgUrl]);
     }


     /**
     * @Route("/prelisting", name="kamada_prelisting_page", options={"main" = true })
     */

     public function kamadaPrelistingPageAction(Request $request)
     {
         $user = $this->get('security.token_storage')->getToken()->getUser();
         $hostIp = $this->getParameter('host_ip');
         $imgUrl = $this->getParameter('img_url');
 
         return $this->render('template/kamada/prelisting.html.twig', ['user' => $user, "hostIp" => $hostIp, 'imgUrl' => $imgUrl]);
     }


      /**
     * @Route("/ajax_post_kamada_header", 
     * 	name="ajax_post_kamada_header",
     *	options={"expose" = true}
     * )
     * @Method("POST")
     */

    public function ajaxPostKamadaHeaderAction(Request $request)
    {
        $user = $this->get("security.token_storage")->getToken()->getUser();
        $em = $this->getDoctrine()->getManager("electPrep2024");
        
        $entity = new KamadaHeader();
        $entity->setProVoterId($request->get('proVoterId'));
        $entity->setMunicipalityNo($request->get('municipalityNo'));
        $entity->setBarangayNo($request->get('barangayNo'));
        $entity->setAssignedPurok($request->get('assignedPurok'));

        $entity->setCreatedAt(new \DateTime());
        $entity->setCreatedBy($user->getUsername());
        $entity->setUpdatedAt(new \DateTime());
        $entity->setUpdatedBy($user->getUsername());
        $entity->setRemarks($request->get('remarks'));


        $sql = "SELECT * FROM psw_municipality 
        WHERE province_code = ? 
        AND municipality_no = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, 53);
        $stmt->bindValue(2, $entity->getMunicipalityNo());
        $stmt->execute();

        $municipality = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($municipality != null)
            $entity->setMunicipalityName($municipality['name']);

        $sql = "SELECT * FROM psw_barangay 
        WHERE brgy_code = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, 53 . $entity->getMunicipalityNo() . $entity->getBarangayNo());
        $stmt->execute();

        $barangay = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($barangay != null)
            $entity->setBarangayName($barangay['name']);

        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy(['proVoterId' => intval($request->get('proVoterId'))]);

        if ($proVoter) {

            $proIdCode = !empty($proVoter->getProIdCode()) ? $proVoter->getProIdCode() : $this->generateProIdCode($proVoter->getProId(), $proVoter->getVoterName(), $proVoter->getMunicipalityNo()) ;
            $generatedIdNo = date('Y-m-d') . '-' . $proVoter->getMunicipalityNo() . '-' . $proVoter->getBrgyNo() . '-' . $proIdCode;

            if($proVoter->getGeneratedIdNo() != null && $proVoter->getGeneratedIdNo() != ''){
                $generatedIdNo = $proVoter->getGeneratedIdNo();
            }

            $entity->setProIdCode($proIdCode);
            $proVoter->setProIdCode($proIdCode);
            $proVoter->setGeneratedIdNo($generatedIdNo);

            $entity->setGeneratedIdNo($generatedIdNo);
            $entity->setVoterName($proVoter->getVoterName());
            $entity->setCellphone($request->get('cellphone'));
            $entity->setVoterGroup($request->get('voterGroup'));
            $proVoter->setVoterGroup($request->get('voterGroup'));
            $proVoter->setAssignedPurok($request->get('assignedPurok'));
        }

         
        $tlProVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy(['proVoterId' => intval($request->get('tlProVoterId'))]);

        if ($tlProVoter) {
            $entity->setTlProIdCode($tlProVoter->getProIdCode());
            $entity->setTlGeneratedIdNo($tlProVoter->getGeneratedIdNo());
            $entity->setTlVoterName($tlProVoter->getVoterName());
            $entity->setTlProVoterId($tlProVoter->getProVoterId());
        }

        $validator = $this->get('validator');
        $violations = $validator->validate($entity);

        $errors = [];

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse($errors, 400);
        }

        $duplicateDtl = $em->getRepository("AppBundle:KamadaDetail")
                          ->findOneBy([ 'proVoterId' => $request->get('proVoterId') ]);
        if($duplicateDtl){
            return new JsonResponse(['proVoterId' => "KFC member cannot be encoded as a leader."], 400);
        }

        $sql = "SELECT * FROM psw_municipality 
        WHERE province_code = ? 
        AND municipality_no = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, 53);
        $stmt->bindValue(2, $entity->getMunicipalityNo());
        $stmt->execute();

        $municipality = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($municipality != null)
            $entity->setMunicipalityName($municipality['name']);

        $sql = "SELECT * FROM psw_barangay 
        WHERE brgy_code = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, 53 . $entity->getMunicipalityNo() . $entity->getBarangayNo());
        $stmt->execute();

        $barangay = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($barangay != null)
            $entity->setBarangayName($barangay['name']);
        
        $proVoter->setAsnMunicipalityName($entity->getMunicipalityName());
        $proVoter->setAsnMunicipalityNo($entity->getMunicipalityNo());
        $proVoter->setAsnBarangayName($entity->getBarangayName());
        $proVoter->setAsnBarangayNo($entity->getBarangayNo());
        $proVoter->setHasAttended(1);

        $em->persist($entity);
        $em->flush();
        $em->clear();

        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($entity));
    }

    private function generateProIdCode($proId, $voterName, $municipalityNo)
    {
        $proIdCode = '000001';

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT CAST(RIGHT(pro_id_code ,6) AS UNSIGNED ) AS order_num FROM tbl_project_voter
        WHERE pro_id = ? AND municipality_no = ? ORDER BY order_num DESC LIMIT 1 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $proId);
        $stmt->bindValue(2, $municipalityNo);
        $stmt->execute();

        $request = $stmt->fetch();

        if ($request) {
            $proIdCode = sprintf("%06d", intval($request['order_num']) + 1);
        }

        $namePart = explode(' ', $voterName);
        $uniqueId = uniqid('PHP');

        $prefix = '';

        foreach ($namePart as $name) {
            $prefix .= substr($name, 0, 1);
        }

        return $prefix . $municipalityNo . $proIdCode;
    }
    

    /**
     * @Route("/ajax_patch_kamada_header/{id}", 
     * 	name="ajax_patch_kamada_header",
     *	options={"expose" = true}
     * )
     * @Method("PATCH")
     */

     public function ajaxPatchKamadaHeaderAction($id,Request $request)
     {
         $user = $this->get("security.token_storage")->getToken()->getUser();
         $em = $this->getDoctrine()->getManager("electPrep2024");
         
         $entity = $em->getRepository("AppBundle:KamadaHeader")->find($id);

         if(!$entity)
             return new JsonResponse(null,404);

         $entity->setProVoterId($request->get('proVoterId'));
         $entity->setMunicipalityNo($request->get('municipalityNo'));
         $entity->setBarangayNo($request->get('barangayNo'));
         $entity->setAssignedPurok($request->get('assignedPurok'));
 
         $entity->setUpdatedAt(new \DateTime());
         $entity->setUpdatedBy($user->getUsername());
 
         $sql = "SELECT * FROM psw_municipality 
         WHERE province_code = ? 
         AND municipality_no = ? ";
 
         $stmt = $em->getConnection()->prepare($sql);
         $stmt->bindValue(1, 53);
         $stmt->bindValue(2, $entity->getMunicipalityNo());
         $stmt->execute();
 
         $municipality = $stmt->fetch(\PDO::FETCH_ASSOC);
 
         if ($municipality != null)
             $entity->setMunicipalityName($municipality['name']);
 
         $sql = "SELECT * FROM psw_barangay 
         WHERE brgy_code = ? ";
 
         $stmt = $em->getConnection()->prepare($sql);
         $stmt->bindValue(1, 53 . $entity->getMunicipalityNo() . $entity->getBarangayNo());
         $stmt->execute();
 
         $barangay = $stmt->fetch(\PDO::FETCH_ASSOC);
 
         if ($barangay != null)
             $entity->setBarangayName($barangay['name']);
 
         $proVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy(['proVoterId' => intval($request->get('proVoterId'))]);
 
         if ($proVoter) {
 
             $proIdCode = !empty($proVoter->getProIdCode()) ? $proVoter->getProIdCode() : $this->generateProIdCode($proVoter->getProId(), $proVoter->getVoterName(), $proVoter->getMunicipalityNo()) ;
             $generatedIdNo = date('Y-m-d') . '-' . $proVoter->getMunicipalityNo() . '-' . $proVoter->getBrgyNo() . '-' . $proIdCode;
 
             if($proVoter->getGeneratedIdNo() != null && $proVoter->getGeneratedIdNo() != ''){
                 $generatedIdNo = $proVoter->getGeneratedIdNo();
             }
 
             $entity->setProIdCode($proIdCode);
             $proVoter->setProIdCode($proIdCode);
             $proVoter->setGeneratedIdNo($generatedIdNo);
 
             $entity->setGeneratedIdNo($generatedIdNo);
             $entity->setVoterName($proVoter->getVoterName());
             $entity->setCellphone($request->get('cellphone'));
             $entity->setVoterGroup($request->get('voterGroup'));
             $proVoter->setVoterGroup($request->get('voterGroup'));
             $proVoter->setAssignedPurok($request->get('assignedPurok'));
         }
 
          
         $tlProVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy(['proVoterId' => intval($request->get('tlProVoterId'))]);
 
         if ($tlProVoter) {
             $entity->setTlProIdCode($tlProVoter->getProIdCode());
             $entity->setTlGeneratedIdNo($tlProVoter->getGeneratedIdNo());
             $entity->setTlVoterName($tlProVoter->getVoterName());
             $entity->setTlProVoterId($tlProVoter->getProVoterId());
         }
 
         $validator = $this->get('validator');
         $violations = $validator->validate($entity);
 
         $errors = [];
 
         if (count($violations) > 0) {
             foreach ($violations as $violation) {
                 $errors[$violation->getPropertyPath()] = $violation->getMessage();
             }
             return new JsonResponse($errors, 400);
         }
 
         $duplicateDtl = $em->getRepository("AppBundle:KamadaDetail")
                           ->findOneBy([ 'proVoterId' => $request->get('proVoterId') ]);
         if($duplicateDtl){
             return new JsonResponse(['proVoterId' => "KFC member cannot be encoded as a leader."], 400);
         }
 
         $sql = "SELECT * FROM psw_municipality 
         WHERE province_code = ? 
         AND municipality_no = ? ";
 
         $stmt = $em->getConnection()->prepare($sql);
         $stmt->bindValue(1, 53);
         $stmt->bindValue(2, $entity->getMunicipalityNo());
         $stmt->execute();
 
         $municipality = $stmt->fetch(\PDO::FETCH_ASSOC);
 
         if ($municipality != null)
             $entity->setMunicipalityName($municipality['name']);
 
         $sql = "SELECT * FROM psw_barangay 
         WHERE brgy_code = ? ";
 
         $stmt = $em->getConnection()->prepare($sql);
         $stmt->bindValue(1, 53 . $entity->getMunicipalityNo() . $entity->getBarangayNo());
         $stmt->execute();
 
         $barangay = $stmt->fetch(\PDO::FETCH_ASSOC);
 
         if ($barangay != null)
             $entity->setBarangayName($barangay['name']);
         
         $proVoter->setAsnMunicipalityName($entity->getMunicipalityName());
         $proVoter->setAsnMunicipalityNo($entity->getMunicipalityNo());
         $proVoter->setAsnBarangayName($entity->getBarangayName());
         $proVoter->setAsnBarangayNo($entity->getBarangayNo());
         $proVoter->setHasAttended(1);
 
         $em->persist($entity);
         $em->flush();
         $em->clear();
 
         $serializer = $this->get('serializer');
 
         return new JsonResponse($serializer->normalize($entity));
     }

    /**
     * @Route("/ajax_get_datatable_kamada_header", name="ajax_get_datatable_kamada_header", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */

     public function ajaxGetDatatableKamadaHeaderAction(Request $request)
     {
         $columns = array(
             0 => "h.id",
             1 => "h.voter_name",
             2 => "h.voter_group",
             3 => "h.municipality_name",
             4 => "h.barangay_name",
             5 => "h.cellphone",
             6 => "h.tl_voter_name",
         );
 
         $sWhere = "";
 
         $select['h.voter_name'] = $request->get("voterName");
         $select['h.municipality_name'] = $request->get("municipalityName");
         $select['h.barangay_name'] = $request->get("barangayName");
         $select['h.voter_group'] = $request->get("voterGroup");
         $select['h.cellphone'] = $request->get("cellphone");
         $select['h.tl_voter_name'] = $request->get("tlVoterName");
 
         foreach ($select as $key => $value) {
             $searchValue = $select[$key];
             if ($searchValue != null || !empty($searchValue)) {
                 $sWhere .= " AND " . $key . " LIKE '%" . $searchValue . "%' ";
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
         ;
         $em->getConnection()->getConfiguration()->setSQLLogger(null);
 
         $sql = "SELECT COALESCE(count(h.id),0) FROM tbl_kamada_hdr h ";
         $stmt = $em->getConnection()->query($sql);
         $recordsTotal = $stmt->fetchColumn();
 
         $sql = "SELECT COALESCE(COUNT(h.id),0) FROM tbl_kamada_hdr h 
                 INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = h.pro_voter_id 
                 WHERE 1 ";
 
         $sql .= $sWhere . ' ' . $sOrder;
         $stmt = $em->getConnection()->query($sql);
         $recordsFiltered = $stmt->fetchColumn();
 
         $sql = "SELECT h.* FROM tbl_kamada_hdr h 
             INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = h.pro_voter_id 
             WHERE 1 " . $sWhere . ' ' . $sOrder . " LIMIT {$length} OFFSET {$start} ";
 
         $stmt = $em->getConnection()->query($sql);
         $data = [];
 
         while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
             $data[] = $row;
         }
 
         foreach ($data as &$row) {
            //  $sql = "SELECT COUNT(pv.pro_voter_id)  as total_members,
            //          COALESCE(COUNT(CASE WHEN pv.is_non_voter = 0 then 1 end),0) as total_voters,
            //          COALESCE(COUNT(CASE WHEN pv.is_non_voter = 1 then 1 end),0) as total_non_voters
 
            //          FROM tbl_household_dtl hd 
            //          INNER JOIN tbl_project_voter pv 
            //          ON pv.pro_voter_id = hd.pro_voter_id 
            //          WHERE hd.household_id = ? ";
            //  $stmt = $em->getConnection()->prepare($sql);
            //  $stmt->bindValue(1, $row['id']);
            //  $stmt->execute();
 
            //  $summary = $stmt->fetch(\PDO::FETCH_ASSOC);
 
 
            //  $row['total_members'] = $summary['total_members'] + 1;
            //  $row['total_voters'] = $row['is_non_voter'] != 1 ? $summary['total_voters'] + 1 : $summary['total_voters'];
            //  $row['total_non_voters'] = $row['is_non_voter'] == 1 ? $summary['total_non_voters'] + 1 : $summary['total_non_voters'];
         }
 
         $draw = (null !== $request->query->get('draw')) ? $request->query->get('draw') : 0;
         $res['data'] = $data;
         $res['recordsTotal'] = $recordsTotal;
         $res['recordsFiltered'] = $recordsFiltered;
         $res['draw'] = $draw;
 
         return new JsonResponse($res);
     }
 
 
     /**
     * @Route("/ajax_get_kamada_header/{id}",
     *       name="ajax_get_kamada_header",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetKamadaHeader($id)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");
        ;
        $entity = $em->getRepository("AppBundle:KamadaHeader")
            ->find($id);

        if (!$entity) {
            return new JsonResponse(['message' => 'not found']);
        }

        $serializer = $this->get("serializer");
        $entity = $serializer->normalize($entity);
      
        return new JsonResponse($entity);
    }

      /**
     * @Route("/ajax_select2_kamada_batch_no",
     *       name="ajax_select2_kamada_batch_no",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

     public function ajaxSelect2KamadaBatchNo(Request $request)
     {
         $em = $this->getDoctrine()->getManager("electPrep2024");
         $searchText = trim(strtoupper($request->get('searchText')));
         $searchText = '%' . strtoupper($searchText) . '%';
 
         $sql = "SELECT DISTINCT batch_no FROM tbl_kamada_dtl d WHERE d.batch_no LIKE ? ORDER BY d.batch_no ASC LIMIT 30";
         $stmt = $em->getConnection()->prepare($sql);
         $stmt->bindValue(1, $searchText);
         $stmt->execute();
 
         $data = $stmt->fetchAll();
 
         if (count($data) <= 0) {
             return new JsonResponse(array());
         }
 
         $em->clear();
 
         return new JsonResponse($data);
     }


      /**
     * @Route("/ajax_post_kamada_detail", 
     * 	name="ajax_post_kamada_detail",
     *	options={"expose" = true}
     * )
     * @Method("POST")
     */

    public function ajaxPostKamadaDetailAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");
        
        $user = $this->get("security.token_storage")->getToken()->getUser();

        $hdr = $em->getRepository("AppBundle:KamadaHeader")
        ->find($request->get('hdrId'));

        if(!$hdr)
            return new JsonResponse(['message' => 'household not found...'], 404);

        $entity = new KamadaDetail();
        $entity->setHdrId($request->get('hdrId'));
        $entity->setProVoterId($request->get('proVoterId'));
        $entity->setMunicipalityNo($request->get('municipalityNo'));
        $entity->setBarangayNo($request->get('barangayNo'));
        $entity->setCellphone(trim($request->get('cellphone')));
        $entity->setVoterGroup(trim(strtoupper($request->get('voterGroup'))));
        $entity->setBatchNo(trim(strtoupper($request->get('batchNo'))));
        $entity->setAssignedPurok($hdr->getAssignedPurok());

        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy(['proVoterId' => intval($request->get('proVoterId'))]);

        if ($proVoter) {

            $proIdCode = !empty($proVoter->getProIdCode()) ? $proVoter->getProIdCode() : $this->generateProIdCode($proVoter->getProId(), $proVoter->getVoterName(), $proVoter->getMunicipalityNo()) ;
            $generatedIdNo = date('Y-m-d') . '-' . $proVoter->getMunicipalityNo() . '-' . $proVoter->getBrgyNo() . '-' . $proIdCode;

            if($proVoter->getGeneratedIdNo() != null && $proVoter->getGeneratedIdNo() != ''){
                $generatedIdNo = $proVoter->getGeneratedIdNo();
            }

            $proVoter->setGeneratedIdNo($generatedIdNo);
            $proVoter->setProIdCode($proIdCode);
            $proVoter->setAssignedPurok($hdr->getAssignedPurok());

            $entity->setVoterName($proVoter->getVoterName());
            $entity->setProIdCode($proVoter->getProIdCode());
            $entity->setGeneratedIdNo($generatedIdNo);
        }

        $entity->setCreatedAt(new \DateTime());
        $entity->setCreatedBy($user->getUsername());
        $entity->setRemarks($request->get('remarks'));
        $entity->setStatus('A');

        $validator = $this->get('validator');
        $violations = $validator->validate($entity);

        $errors = [];

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse($errors, 400);
        }

        if ($proVoter) {
            if (!empty($entity->getCellphone()))
                $proVoter->setCellphone($entity->getCellphone());

            $proVoter->setVoterGroup($entity->getVoterGroup());
        }

        $duplicatHdr = $em->getRepository("AppBundle:KamadaHeader")
                          ->findOneBy([ 'proVoterId' => $request->get('proVoterId') ]);

        if($duplicatHdr){
            return new JsonResponse(['proVoterId' => 'KFC leader cannot be added as a member.'],400);
        }

        $sql = "SELECT * FROM psw_municipality 
        WHERE province_code = ? 
        AND municipality_no = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, 53);
        $stmt->bindValue(2, $entity->getMunicipalityNo());
        $stmt->execute();

        $municipality = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($municipality != null)
            $entity->setMunicipalityName($municipality['name']);

        $sql = "SELECT * FROM psw_barangay 
        WHERE brgy_code = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, 53 . $entity->getMunicipalityNo() . $entity->getBarangayNo());
        $stmt->execute();

        $barangay = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($barangay != null)
            $entity->setBarangayName($barangay['name']);
          
        $em->persist($entity);
        $em->flush();
        $em->clear();

        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($entity));
    }

    /**
     * @Route("/ajax_get_datatable_kamada_detail", name="ajax_get_datatable_kamada_detail", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */

     public function ajaxGetDatatableKamadaDetailAction(Request $request)
     {
         $columns = array(
             0 => "d.id",
             1 => "d.voter_name",
             2 => "d.voter_group",
             3 => "d.barangay_name",
             4 => "d.cellphone"
         );
 
         $sWhere = "";
 
         $select['d.hdr_id'] = $request->get('hdrId');
         $select['d.voter_name'] = $request->get("voterName");
         $hdrId = $request->get('hdrId');
 
         foreach ($select as $key => $value) {
             $searchValue = $select[$key];
             if ($searchValue != null || !empty($searchValue)) {
                 $sWhere .= " AND " . $key . " LIKE '%" . $searchValue . "%'";
             }
         }
 
 
         $sWhere .= " AND d.hdr_id = ${hdrId} ";
 
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
         ;
         $em->getConnection()->getConfiguration()->setSQLLogger(null);
 
         $sql = "SELECT COALESCE(count(d.id),0) FROM tbl_kamada_dtl d WHERE d.hdr_id = ${hdrId}";
         $stmt = $em->getConnection()->query($sql);
         $recordsTotal = $stmt->fetchColumn();
 
         $sql = "SELECT COALESCE(COUNT(d.id),0) FROM tbl_kamada_dtl d WHERE 1 ";
 
         $sql .= $sWhere . ' ' . $sOrder;
         $stmt = $em->getConnection()->query($sql);
         $recordsFiltered = $stmt->fetchColumn();
 
         $sql = "SELECT d.* FROM tbl_kamada_dtl d 
             WHERE 1 " . $sWhere . ' ' . $sOrder . " LIMIT {$length} OFFSET {$start} ";
 
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
     * @Route("/ajax_delete_kamada_detail/{id}", 
     * 	name="ajax_delete_kamada_detail",
     *	options={"expose" = true}
     * )
     * @Method("DELETE")
     */

    public function ajaxDeleteHouseholdDetailAction($id)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");
    
        $entity = $em->getRepository("AppBundle:KamadaDetail")->find($id);

        if (!$entity)
            return new JsonResponse(null, 404);

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null, 200);
    }

     /**
     * @Route("/ajax_delete_kamada_header/{id}", 
     * 	name="ajax_delete_kamada_header",
     *	options={"expose" = true}
     * )
     * @Method("DELETE")
     */

     public function ajaxDeleteHouseholdHeaderAction($id)
     {
         $em = $this->getDoctrine()->getManager("electPrep2024");
     
         $entity = $em->getRepository("AppBundle:KamadaHeader")->find($id);
 
         if (!$entity)
             return new JsonResponse(null, 404);
 
         $entities = $em->getRepository('AppBundle:KamadaDetail')->findBy([
             'hdrId' => $entity->getId()
         ]);
 
         foreach ($entities as $detail) {
             $em->remove($detail);
         }
 
         $em->remove($entity);
         $em->flush();
 
         return new JsonResponse(null, 200);
     }
 

       /**
    * @Route("/ajax_post_kamada_prelisting", 
    * 	name="ajax_post_kamada_prelisting",
    *	options={"expose" = true}
    * )
    * @Method("POST")
    */

    public function ajaxPostKamadaPrelistingAction(Request $request){

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $entity = new PrelistingHeader();
    	$entity->setPrelistingDesc(strtoupper($request->get('prelistingDesc')));
        $entity->setPrelistingDate($request->get('prelistingDate'));
        $entity->setIsPrinted(0);
        $entity->setUpdatedAt(new \DateTime());
        $entity->setUpdatedBy($user->getUsername());
        $entity->setCreatedAt(new \DateTime());
        $entity->setCreatedBy($user->getUsername());
        $entity->setRemarks($request->get('remarks'));
    	$entity->setStatus('A');

    	$validator = $this->get('validator');
        $violations = $validator->validate($entity);

        $errors = [];

        if(count($violations) > 0){
            foreach( $violations as $violation ){
                $errors[$violation->getPropertyPath()] =  $violation->getMessage();
            }
            return new JsonResponse($errors,400);
        }

        $em = $this->getDoctrine()->getManager("electPrep2024");

        $em->persist($entity);
        $em->flush();
    	$em->clear();

    	$serializer = $this->get('serializer');

    	return new JsonResponse($serializer->normalize($entity));
    }

    /**
     * @Route("/ajax_get_datatable_kamada_prelisting", name="ajax_get_datatable_kamada_prelisting", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */

	public function ajaxGetDatatableKamadaPrelistingAction(Request $request)
	{	
        $columns = array(
            0 => "e.id",
            1 => "e.prelisting_desc",
            2 => "e.prelisting_date"
        );

        $sWhere = "";
    
        $select['e.prelisting_desc'] = $request->get('prelisting_desc');

        foreach($select as $key => $value){
            $searchValue = $select[$key];
            if($searchValue != null || !empty($searchValue)) {
                $sWhere .= " AND " . $key . " LIKE '%" . $searchValue . "%'";
            }
        }
        
        $sOrder = "";

        if(null !== $request->query->get('order')){
            $sOrder = "ORDER BY  ";
            for ( $i=0 ; $i<intval(count($request->query->get('order'))); $i++ )
            {
                if ( $request->query->get('columns')[$request->query->get('order')[$i]['column']]['orderable'] )
                {
                    $selected_column = $columns[$request->query->get('order')[$i]['column']];
                    $sOrder .= " ".$selected_column." ".
                        ($request->query->get('order')[$i]['dir']==='asc' ? 'ASC' : 'DESC') .", ";
                }
            }

            $sOrder = substr_replace( $sOrder, "", -2 );
            if ( $sOrder == "ORDER BY" )
            {
                $sOrder = "";
            }
        }

        $start = 1;
        $length = 1;

        if(null !== $request->query->get('start') && null !== $request->query->get('length')){
            $start = intval($request->query->get('start'));
            $length = intval($request->query->get('length'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(e.id),0) FROM tbl_pre_listing_hdr e ";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(e.id),0) FROM tbl_pre_listing_hdr e
                WHERE 1 ";

        $sql .= $sWhere . ' ' . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT e.* FROM tbl_pre_listing_hdr e 
            WHERE 1 " . $sWhere . ' ' . $sOrder . " LIMIT {$length} OFFSET {$start} ";

        $stmt = $em->getConnection()->query($sql);
        $data = [];

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $data[] = $row;
        }

        $draw = (null !== $request->query->get('draw')) ? $request->query->get('draw') : 0;
		$res['data'] =  $data;
	    $res['recordsTotal'] = $recordsTotal;
	    $res['recordsFiltered'] = $recordsFiltered;
        $res['draw'] = $draw;

	    return new JsonResponse($res);
    }

     /**
    * @Route("/ajax_get_kamada_headers/{municipalityNo}/{brgyNo}", 
    *   name="ajax_get_kamada_headers",
    *   options={"expose" = true}
    * )
    * @Method("GET")
    */

    public function ajaxGetKamadaHeaders(Request $request,$municipalityNo,$brgyNo){
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT * FROM tbl_kamada_hdr h
                WHERE (h.municipality_no = ? OR ? IS NULL)
                AND (h.barangay_no = ? OR ? IS NULL )
                ORDER BY h.municipality_name ASC, h.barangay_name ASC, h.voter_name ASC ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, empty($municipalityNo) ? null : $municipalityNo );
        $stmt->bindValue(2, empty($municipalityNo) ? null : $municipalityNo);
        $stmt->bindValue(3, empty($brgyNo) ? null : $brgyNo);
        $stmt->bindValue(4, empty($brgyNo) ? null : $brgyNo);
        $stmt->execute();
        
        $data = array();
        
        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $data[] = $row;
        }

        $em->clear();

        return new JsonResponse($data);
    }

      /**
    * @Route("/ajax_post_kamada_prelisting_detail/{id}",
    *     name="ajax_post_kamada_prelisting_detail",
    *     options={"expose" = true})
    *
    * @Method("POST")
    */

    public function postPrintAction(Request $request,$id){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        
        $hdr = $em->getRepository("AppBundle:PrelistingHeader")->find($id);
 
        if (!$hdr)
            return new JsonResponse(null, 404);

        $profiles = $request->get('profiles');

        if(count($profiles) <= 0){
            return new JsonResponse(['profiles' => "Profiles cannot be empty"],400);
        }
        
        foreach($profiles as $profile){
            $kamadaHdr = $em->getRepository("AppBundle:KamadaHeader")->find($profile);

            if($kamadaHdr){

                $sql = "SELECT group_no FROM tbl_pre_listing_dtl where hdr_id = ? AND municipality_no = ? AND barangay_no = ? ORDER BY group_no DESC";
                
                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $id );
                $stmt->bindValue(2, $kamadaHdr->getMunicipalityNo());
                $stmt->bindValue(3, $kamadaHdr->getBarangayNo());
                $stmt->execute();
                
                $res = $stmt->fetch(\PDO::FETCH_ASSOC);
                $groupNo = 0;

                if($res['group_no'] != null && ((int)$res['group_no']) != 0){
                    $groupNo =  ((int)$res['group_no']) + 1;
                }else{
                    $groupNo = 1;
                }

                $entity = new PrelistingDetail();
                $entity->setHdrId($id);
                $entity->setProVoterId($kamadaHdr->getProVoterId());
                $entity->setProIdCode($kamadaHdr->getProIdCode());
                $entity->setGeneratedIdNo($kamadaHdr->getGeneratedIdNo());
                $entity->setVoterName($kamadaHdr->getVoterName());
                $entity->setMunicipalityName($kamadaHdr->getMunicipalityName());
                $entity->setMunicipalityNo($kamadaHdr->getMunicipalityNo());
                $entity->setBarangayName($kamadaHdr->getBarangayName());
                $entity->setBarangayNo($kamadaHdr->getBarangayNo());
                $entity->setVoterGroup($kamadaHdr->getVoterGroup());
                $entity->setGroupNo($groupNo);
                $entity->setIsLeader(1);
        
                $entity->setUpdatedAt(new \DateTime());
                $entity->setUpdatedBy($user->getUsername());
                $entity->setCreatedAt(new \DateTime());
                $entity->setCreatedBy($user->getUsername());
                $entity->setRemarks($request->get('remarks'));
                $entity->setStatus('A');
                        
                $validator = $this->get('validator');

                $violations = [];
                $violations = $validator->validate($entity);

                $errors = [];

                if(count($violations) <= 0){
                    $em->persist($entity);
                }

                $kamadaDtl = $em->getRepository("AppBundle:KamadaDetail")->findBy([
                    'hdrId' => $profile
                ]);

                foreach($kamadaDtl as $dtl){
                    $entity = new PrelistingDetail();
                    $entity->setHdrId($id);
                    $entity->setProVoterId($dtl->getProVoterId());
                    $entity->setProIdCode($dtl->getProIdCode());
                    $entity->setGeneratedIdNo($dtl->getGeneratedIdNo());
                    $entity->setVoterName($dtl->getVoterName());
                    $entity->setMunicipalityName($dtl->getMunicipalityName());
                    $entity->setMunicipalityNo($dtl->getMunicipalityNo());
                    $entity->setBarangayName($dtl->getBarangayName());
                    $entity->setBarangayNo($dtl->getBarangayNo());
                    $entity->setVoterGroup($dtl->getVoterGroup());
                    $entity->setGroupNo($groupNo);
                    $entity->setIsLeader(0);

                    $entity->setUpdatedAt(new \DateTime());
                    $entity->setUpdatedBy($user->getUsername());
                    $entity->setCreatedAt(new \DateTime());
                    $entity->setCreatedBy($user->getUsername());
                    $entity->setRemarks($request->get('remarks'));
                    $entity->setStatus('A');

                    $validator = $this->get('validator');
                    $violations = [];
                    $violations = $validator->validate($entity);

                    $errors = [];

                    if(count($violations) <= 0){
                        $em->persist($entity);
                    }
                }
            }

            $em->flush();
            $em->clear();
        }

        return new JsonResponse($profiles);
    }

      /**
     * @Route("/ajax_datatable_kamada_prelisting_detail",
     *     name="ajax_datatable_kamada_prelisting_detail",
     *     options={"expose" = true}
     *     )
     * @Method("GET")
     */

     public function datatableKamadaPrelistingDetailAction(Request $request)
     {
         $user = $this->get('security.token_storage')->getToken()->getUser();
         $id = $request->get("id");
         
         $filters = array();
         $filters['d.hdr_id'] = $request->get('id');

         $columns = array(
             0 => 'pv.voter_id',
             1 => 'pv.voter_name',
             2 => 'pv.voter_group'
         );
 
 
         $whereStmt = " AND (";
         
         foreach($filters as $field => $searchText){
             if($searchText != ""){
                 if($field == 'd.hdr_id' ){
                     $whereStmt .= "{$field} = '{$searchText}' AND "; 
                 }else{
                     $whereStmt .= "{$field} LIKE '%{$searchText}%' AND "; 
                }
             }
         }
 
         $whereStmt = substr_replace($whereStmt,"",-4);
 
         if($whereStmt == " A"){
             $whereStmt = "";
         }else{
             $whereStmt .= ")";
         }
 
         $orderStmt = "";
 
         if(null !== $request->query->get('order'))
             $orderStmt = $this->genOrderStmt($request,$columns);
 
         $orderStmt = " ORDER BY d.barangay_name ASC ,d.group_no ASC , d.is_leader DESC, d.voter_name ASC";
         
         $start = 0;
         $length = 1;
 
         if(null !== $request->query->get('start') && null !== $request->query->get('length')){
             $start = intval($request->query->get('start'));
             $length = intval($request->query->get('length'));
         }
         
         $em = $this->getDoctrine()->getManager();
         $em->getConnection()->getConfiguration()->setSQLLogger(null);
 
         $sql = "SELECT COALESCE(count(d.id),0) FROM tbl_pre_listing_dtl d WHERE d.hdr_id = {$id} ";
         $stmt = $em->getConnection()->query($sql);
         
         $recordsTotal = $stmt->fetchColumn();
 
         $sql = "SELECT COALESCE(COUNT(d.id),0) FROM tbl_pre_listing_dtl d 
                 WHERE 1 ";
        
 
         $sql .= $whereStmt . ' ' . $orderStmt;
         $stmt = $em->getConnection()->query($sql);
         $recordsFiltered = $stmt->fetchColumn();
         
         $sql = "SELECT d.* FROM tbl_pre_listing_dtl d 
                 WHERE 1 " . $whereStmt . ' ' . $orderStmt . " LIMIT {$length} OFFSET {$start} ";
 
         $stmt = $em->getConnection()->query($sql);
         $data = [];
 
         while($row =  $stmt->fetch(\PDO::FETCH_ASSOC))
         {   
             $data[] = $row;
         }
 
         $draw = (null !== $request->query->get('draw')) ? $request->query->get('draw') : 0;
         $res['data'] =  $data;
         $res['recordsTotal'] = $recordsTotal;
         $res['recordsFiltered'] = $recordsFiltered;
         $res['draw'] = $draw;
 
         $em->clear();
 
         return  new JsonResponse($res);
     }

     private function genOrderStmt($request,$columns){

        $orderStmt = "ORDER BY  ";

        for ( $i=0 ; $i<intval(count($request->query->get('order'))); $i++ )
        {
            if ( $request->query->get('columns')[$request->query->get('order')[$i]['column']]['orderable'] )
            {
                $orderStmt .= " ".$columns[$request->query->get('order')[$i]['column']]." ".
                    ($request->query->get('order')[$i]['dir']==='asc' ? 'ASC' : 'DESC') .", ";
            }
        }

        $orderStmt = substr_replace( $orderStmt, "", -2 );
        if ( $orderStmt == "ORDER BY" )
        {
            $orderStmt = "";
        }

        return $orderStmt;
    }

     /**
    * @Route("/ajax_delete_prelisting_detail/{id}", 
    * 	name="ajax_delete_prelisting_detail",
    *	options={"expose" = true}
    * )
    * @Method("DELETE")
    */

    public function ajaxDeleteKforceDetailAction($id){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:PrelistingDetail")->find($id);

        if(!$entity)
            return new JsonResponse(null,404);

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null,200);
    }

    /**
    * @Route("/ajax_delete_kamada_prelisting_header/{id}", 
    * 	name="ajax_delete_kamada_prelisting_header",
    *	options={"expose" = true}
    * )
    * @Method("DELETE")
    */

    public function ajaxDeleteKamadaPrelistingHeaderAction($id){

        $em = $this->getDoctrine()->getManager("electPrep2024");
        $entity = $em->getRepository("AppBundle:PrelistingHeader")->find($id);

        if(!$entity)
            return new JsonResponse(null,404);

        $entities = $em->getRepository('AppBundle:PrelistingDetail')->findBy([
            'hdrId' => $entity->getId()
        ]);

        foreach($entities as $detail){
            $em->remove($detail);
        }

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null,200);
    }

     /**
     * @Route("/ajax_kamada_select2_purok",
     *       name="ajax_kamada_select2_purok",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

     public function ajaxKamadaSelect2Purok(Request $request)
     {
         $em = $this->getDoctrine()->getManager("electPrep2024");
         $municipalityNo = $request->get('municipalityNo');
         $brgyNo = $request->get('brgyNo');
 
         $searchText = trim(strtoupper($request->get('searchText')));
         $searchText = '%' . strtoupper($searchText) . '%';
 
         $sql = "SELECT DISTINCT pv.assigned_purok 
                 FROM tbl_project_voter pv
                 WHERE pv.assigned_purok LIKE ? 
                 ORDER BY pv.assigned_purok ASC LIMIT 30";
 
         $stmt = $em->getConnection()->prepare($sql);
         $stmt->bindValue(1, $searchText);
         $stmt->execute();
 
         $data = $stmt->fetchAll();
 
         if (count($data) <= 0) {
             return new JsonResponse(array());
         }
 
         $em->clear();
 
         return new JsonResponse($data);
     }
 
   /**
    * @Route("/ajax_get_kamada_header/{id}", 
    * 	name="ajax_get_kamada_header",
    *	options={"expose" = true}
    * )
    * @Method("GET")
    */

    public function ajaxGetKforceAction($id){
        $em = $this->getDoctrine()->getManager("electPrep2024");
        $entity = $em->getRepository("AppBundle:KamadaHeader")->find($id);

        if(!$entity)
            return new JsonResponse(null,404);

        $serializer = $this->get("serializer");
        
        return new JsonResponse($serializer->normalize($entity));
    }

}
