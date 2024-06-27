<?php
namespace AppBundle\Controller;

use AppBundle\Entity\AttendanceAssignment;
use AppBundle\Entity\KfcAttendance;
use AppBundle\Entity\KfcAttendanceDetail;
use AppBundle\Entity\OrganizationHierarchy;
use Proxies\__CG__\AppBundle\Entity\AttendanceProfile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @Route("/hierarchy")
 */

class HierarchyController extends Controller
{
    /**
     * @Route("", name="hierarchy_index", options={"main" = true })
     */

    public function HierarchyAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');
        $imgUrl = $this->getParameter('img_url');
        $reportUrl = $this->getParameter('report_url');

        return $this->render(
            'template/hierarchy/index.html.twig',
            [
                'user' => $user,
                'hostIp' => $hostIp,
                'imgUrl' => $imgUrl,
                'reportUrl' => $reportUrl
            ]
        );
    }

    /**
     * @Route("/ajax_get_hierarchy_sample_data", 
     * name="ajax_get_hierarchy_sample_data", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxGetSampleDataAction(Request $request)
    {
        $leaderId = $request->get('leaderId');
        $voterGroupFilter = $request->get('voterGroupFilter');
        $voterGroupFilter = empty($voterGroupFilter) ? "TOP LEADER" : $voterGroupFilter;

        $municipalityNo = $request->get('municipalityNo');
        $barangayNo = $request->get('barangayNo');

        if((empty($barangayNo) || $barangayNo == null) && $leaderId == null)
            return new JsonResponse([]);

        $em = $this->getDoctrine()->getManager("electPrep2024");
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT h.*, pv.position,
                (SELECT COALESCE(COUNT(ap.pro_voter_id),0) FROM tbl_attendance_detail a
                LEFT JOIN tbl_attendance_profile ap 
                ON ap.hdr_id = a.id
                WHERE a.pro_voter_id = h.pro_voter_id
                ) AS total_household_members 
                FROM tbl_organization_hierarchy h 
                INNER JOIN tbl_project_voter pv 
                ON pv.pro_voter_id = h.pro_voter_id
                WHERE (h.pro_voter_id = ? OR ? IS NULL) 
                AND (h.voter_group = ? OR ? IS NULL ) 
                AND (h.municipality_no = ? OR ? IS NULL)
                AND (h.barangay_no = ? OR ? IS NULL )
                ORDER BY h.voter_name ASC ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $leaderId);
        $stmt->bindValue(2, empty($leaderId) ? null : $leaderId);
        $stmt->bindValue(3, $voterGroupFilter);
        $stmt->bindValue(4, empty($voterGroupFilter) ? null : $voterGroupFilter);
        $stmt->bindValue(5, $municipalityNo);
        $stmt->bindValue(6, empty($municipalityNo) ? null : $municipalityNo);
        $stmt->bindValue(7, $barangayNo);
        $stmt->bindValue(8, empty($barangayNo) ? null : $barangayNo);
        $stmt->execute();

        $data = [];
        $counter = 0;

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $children = $this->getChildNodes($row['pro_voter_id']);
            $counter++;

            $profileLabel = ($row['position'] == null || empty($row['position']) || $row['position'] == '' ) ? 'No household profile.' : $row['position'];

            $data[] = [
                "id" => $row['pro_voter_id'],
                'name' => $counter . '. ' . $row['voter_group'] . ': ' . $row['voter_name'] . ' | ' . $row['barangay_name'] . '|' . $profileLabel ,
                'data' => [
                    'voter_group' => $row['voter_group']
                ],
                'voter_group' => $row['voter_group'],
                'children' => $children
            ];
        }

        return new JsonResponse($data);

    }

    private function getChildNodes($id)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");
        $sql = "SELECT h.*, pv.position,
                (SELECT COALESCE(COUNT(ap.pro_voter_id),0) FROM tbl_attendance_detail a
                LEFT JOIN tbl_attendance_profile ap 
                ON ap.hdr_id = a.id
                WHERE a.pro_voter_id = h.pro_voter_id
                ) AS total_household_members  
                from tbl_organization_hierarchy h
                INNER JOIN tbl_project_voter pv 
                ON h.pro_voter_id = pv.pro_voter_id
                WHERE h.parent_node = ? ORDER BY h.voter_name ASC ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $id);
        $stmt->execute();

        $entities = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $data = [];

        if (empty($entities) || count($entities) <= 0)
            return [];

        $counter = 0;

        foreach ($entities as $entity) {
            $counter++;

            $profileLabel = ($entity['position'] == null || empty($entity['position']) || $entity['position'] == '' ) ? 'No household profile.' : $entity['position'];

            $item = [
                'id' => $entity['pro_voter_id'],
                'name' => $counter . '. ' . $entity['voter_group'] . ': ' . $entity['voter_name'] . ' | ' . $entity['barangay_name'] . '|'. $profileLabel,
            ];

            $item['children'] = $this->getChildNodes($entity['pro_voter_id']);
            $data[] = $item;
        }

        return $data;
    }

    /**
     * @Route("/ajax_hierarchy_post_item", 
     *       name="ajax_hierarchy_post_item",
     *		options={ "expose" = true }
     * )
     * @Method("POST")
     */

    public function ajaxHierarchyPostItem(Request $request)
    {

        $em = $this->getDoctrine()->getManager("electPrep2024");
        $proVoterId = $request->get('proVoterId');
        $parentId = $request->get("parentId");

        //fixing bug on hierarchy

        $voter = $em->getRepository("AppBundle:ProjectVoter")->find($proVoterId);
        $user = $this->get("security.token_storage")->getToken()->getUser();

        if (!$voter)
            return new JsonResponse(null, 404);

            
        if($parentId != null && $parentId != 0 ){
            $parent = $em->getRepository("AppBundle:OrganizationHierarchy")->findOneBy([
                'proVoterId' => $parentId
            ]);

            if (!$parent)
                return new JsonResponse(['message' => "invalid parent id."], 404);
        }

        $entity = new OrganizationHierarchy();
        $entity->setProVoterId($voter->getProVoterId());
        $entity->setParentNode($parentId);
        $entity->setMunicipalityName($voter->getMunicipalityName());
        $entity->setMunicipalityNo($voter->getMunicipalityNo());
        $entity->setBarangayName($voter->getBarangayName());
        $entity->setBarangayNo($voter->getBrgyNo());
        $entity->setVoterName($voter->getVoterName());
        $entity->setVoterGroup($request->get('voterGroup'));
        $entity->setAssignedMunNo($request->get('assignedMunNo'));
        $entity->setAssignedBrgyNo($request->get('assignedBrgyNo'));
        $entity->setAssignedPurok(strtoupper($request->get('assignedPurok')));

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

        $assignedMunicipality = $this->getMunicipalityName(53, $request->get('assignedMunNo'));
        $assignedBarangay = $this->getBarangayName($assignedMunicipality['municipality_code'], $request->get('assignedBrgyNo'));

        $entity->setAssignedMunicipality($assignedMunicipality['name']);
        $entity->setAssignedBarangay(($assignedBarangay['name']));

        $em->persist($entity);

        $voter->setVoterGroup($request->get('voterGroup'));

        $em->flush();
        $em->clear();

        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($entity), 200);
    }

    private function getMunicipalityName($provinceCode, $municipalityNo)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");
        $sql = "SELECT * from psw_municipality m
                WHERE m.province_code = ? AND m.municipality_no = ? LIMIT 1 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->bindValue(2, $municipalityNo);
        $stmt->execute();

        $entity = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $entity;
    }

    private function getBarangayName($municipalityCode, $brgyNo)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");
        $sql = "SELECT * from psw_barangay b
                WHERE b.municipality_code = ? AND b.brgy_no = ? LIMIT 1 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipalityCode);
        $stmt->bindValue(2, $brgyNo);
        $stmt->execute();

        $entity = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $entity;
    }

    /**
     * @Route("/ajax_hierarchy_patch_item",
     *     name="ajax_hierarchy_patch_item",
     *    options={"expose" = true}
     * )
     * @Method("PATCH")
     */

    public function ajaxPatchHierarchyItemAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $proVoterId = $request->get('proVoterId');
        $parentId = $request->get('parentId');

        $entity = $em->getRepository("AppBundle:OrganizationHierarchy")->findOneBy([
            'proVoterId' => $proVoterId
        ]);

        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy([
            'proVoterId' => $proVoterId
        ]);

        if (!$entity) {
            return new JsonResponse([], 404);
        }

        $nodeLevel = $request->get('nodeLevel');
        $voterGroup = "";

        switch ($nodeLevel) {
            case 1:
                $voterGroup = "TOP LEADER";
                break;
            case 2:
                $voterGroup = "KCL0";
                break;
            case 3:
                $voterGroup = "KCL1";
                break;
            case 4:
                $voterGroup = "KCL2";
                break;
            case 5:
                $voterGroup = "KCL3";
                break;
        }

        $entity->setParentNode($parentId);
        //$entity->setVoterGroup($voterGroup);

        $validator = $this->get('validator');
        $violations = $validator->validate($entity);

        $errors = [];

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse($errors, 400);
        }

        //$proVoter->setVoterGroup($voterGroup);

        $em->flush();
        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($entity));
    }

    /**
     * @Route("/ajax_hierarchy_select2_project_voters", 
     *       name="ajax_hierarchy_select2_project_voters",
     *		options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2HierarchyProjectVoters(Request $request)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $electId = $request->get("electId");
        $provinceCode = $request->get("provinceCode");
        $municipalityNo = $request->get("municipalityNo");
        $brgyNo = $request->get("brgyNo");
        $voterGroup = $request->get("voterGroup");
        $voterGroup = empty($voterGroup) ? "TOP LEADER" : $voterGroup;

        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT p.* FROM tbl_project_voter p  
                INNER JOIN tbl_organization_hierarchy h 
                ON h.pro_voter_id = p.pro_voter_id 
                WHERE p.voter_name LIKE ? 
                AND p.province_code = ? 
                AND p.elect_id = ? 
                AND (p.municipality_no = ? OR ? IS NULL)
                AND (p.brgy_no = ? OR ? IS NULL)
                AND (p.voter_group = ? )
                ORDER BY p.voter_name ASC LIMIT 10";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->bindValue(2, $provinceCode);
        $stmt->bindValue(3, $electId);
        $stmt->bindValue(4, $municipalityNo);
        $stmt->bindValue(5, empty($municipalityNo) ? null : $municipalityNo);
        $stmt->bindValue(6, $brgyNo);
        $stmt->bindValue(7, empty($brgyNo) ? null : $brgyNo);
        $stmt->bindValue(8, $voterGroup);
        $stmt->execute();

        $projectVoters = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $projectVoters[] = $row;
        }

        if (count($projectVoters) <= 0)
            return new JsonResponse(array());

        $em->clear();

        return new JsonResponse($projectVoters);
    }

    /**
     * @Route("/ajax_delete_hierarchy_item/{proVoterId}",
     *     name="ajax_delete_hierarchy_item",
     *    options={"expose" = true}
     * )
     * @Method("DELETE")
     */

    public function ajaxDeleteHierarchyAction($proVoterId, Request $request)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");
        $entity = $em->getRepository("AppBundle:OrganizationHierarchy")->findOneBy([
            'proVoterId' => $proVoterId
        ]);

        $projectVoter = $em->getRepository("AppBundle:ProjectVoter")->find($proVoterId);

        if (!$entity) {
            return new JsonResponse(null, 404);
        }

        $this->removeChildren(($proVoterId));

        $projectVoter->setVoterGroup("");

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null, 200);
    }

    private function removeChildren($parentId)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");

        $entities = $em->getRepository("AppBundle:OrganizationHierarchy")->findBy([
            'parentNode' => $parentId
        ]);

        if (count($entities) > 0) {

            foreach ($entities as $entity) {

                $projectVoter = $em->getRepository("AppBundle:ProjectVoter")->find($entity->getProVoterId());
                $projectVoter->setVoterGroup("");

                $em->remove($entity);
                $em->flush();

                $this->removeChildren(($entity->getProVoterId()));
            }
        }
    }

    /**
     * @Route("/ajax_hierarchy_select2_purok",
     *       name="ajax_hierarchy_select2_purok",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxHierarchySelect2Purok(Request $request)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");
        $municipalityNo = $request->get('municipalityNo');
        $brgyNo = $request->get('brgyNo');

        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT h.assigned_purok 
                FROM tbl_organization_hierarchy h 
                WHERE h.assigned_purok LIKE ? 
                ORDER BY h.assigned_purok ASC LIMIT 30";

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
     * @Route("/ajax_get_hierarchy_item/{proVoterId}", 
     * 	name="ajax_get_hierarchy_item",
     *	options={"expose" = true}
     * )
     * @Method("GET")
     * @return JsonResponse|Response
     */

    public function ajaxGetHierarchyItem(Request $request, $proVoterId)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");

        $entity = $em->getRepository("AppBundle:OrganizationHierarchy")->findOneBy([
            "proVoterId" => $proVoterId
        ]);

        if (!$entity) {
            return new JsonResponse(null, 404);
        }


        $voter = $em->getRepository("AppBundle:ProjectVoter")->find($proVoterId);

        $serializer = $this->get('serializer');
        $entity = $serializer->normalize($entity);
        $voter = $serializer->normalize($voter);

        $entity['voter'] = $voter;

              
        $sql = "SELECT hd.* FROM tbl_household_hdr hh INNER JOIN tbl_household_dtl hd 
                ON hh.id = hd.household_id  
                where hh.pro_voter_id = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $proVoterId);
        $stmt->execute();
        
        $members = [];

        $members = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $entity['members'] = $members;
    
        return new JsonResponse($entity, 200);
    }


    /**
     * @Route("/ajax_hierarchy_patch_item_info/{proVoterId}", 
     *       name="ajax_hierarchy_patch_item_info",
     *		options={ "expose" = true }
     * )
     * @Method("PATCH")
     */

    public function ajaxHierarchyPatchItem($proVoterId, Request $request)
    {

        $em = $this->getDoctrine()->getManager("electPrep2024");

        $entity = $em->getRepository("AppBundle:OrganizationHierarchy")->findOneBy([
            "proVoterId" => $proVoterId
        ]);

        $voter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy([
            "proVoterId" => $proVoterId
        ]);

        $user = $this->get("security.token_storage")->getToken()->getUser();

        if (!$entity)
            return new JsonResponse(null, 404);

        $entity->setVoterGroup($request->get('voterGroup'));
        $entity->setContactNo($request->get('contactNo'));
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

        $voter->setVoterGroup($request->get('voterGroup'));
        $voter->setCellphone($request->get('contactNo'));

        $em->flush();
        $em->clear();

        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($entity), 200);
    }

      /**
     * @Route("/ajax_hierarchy_get_item_top_leader/{proVoterId}", 
     *       name="ajax_hierarchy_get_item_top_leader",
     *		options={ "expose" = true }
     * )
     * @Method("GET")
     */

     public function ajaxHierarchyGetItemTopLeader($proVoterId, Request $request)
     {
 
         $em = $this->getDoctrine()->getManager("electPrep2024");
 
         $entity = $em->getRepository("AppBundle:OrganizationHierarchy")->findOneBy([
             "proVoterId" => $proVoterId
         ]);
 
         $voter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy([
             "proVoterId" => $proVoterId
         ]);
 
         $user = $this->get("security.token_storage")->getToken()->getUser();
 
         if (!$entity)
             return new JsonResponse(null, 404);
        
 
         $serializer = $this->get('serializer');
         $parentNode = null;

         if($entity->getParentNode() != null ){
             $parentNode = $this->getParentNode($entity->getParentNode());
         }

         return new JsonResponse($parentNode, 200);
     }

     private function getParentNode($proVoterId){
        $em = $this->getDoctrine()->getManager("electPrep2024");

        
        $sql = "SELECT * FROM tbl_organization_hierarchy where pro_voter_id = ? ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $proVoterId);
        $stmt->execute();
        
        $entity = $stmt->fetch(\PDO::FETCH_ASSOC);
        $parentNode = null;

        if($entity['parent_node'] != null ){
            $parentNode = $this->getParentNode($entity['parent_node']);    
        }

        return $parentNode == null ? $proVoterId : $parentNode;
     }

    /**
     * @Route("/ajax_hierarchy_select2_voter_group",
     *       name="ajax_hierarchy_select2_voter_group",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxHierarchySelect2VoterGroup(Request $request)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT voter_group FROM tbl_project_voter p 
                WHERE elect_id = 423  AND municipality_no IN ('16','01') AND p.voter_group LIKE ? ORDER BY p.voter_group ASC LIMIT 30";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->execute();

        $categories = $stmt->fetchAll();

        if (count($categories) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($categories);
    }

    /**
     * @Route("/ajax_get__hierarchy_datatable_attendance_profile", 
     * name="ajax_get__hierarchy_datatable_attendance_profile", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxGetHierarchyDatatableProfileAction(Request $request)
    {
        $columns = array(
            0 => "h.id",
            1 => "h.voter_name",
            2 => "h.municipality_name",
            3 => "h.barangay_name",
            4 => "h.contact_no"
        );

        $sWhere = "";

        $select['ap.voter_name'] = $request->get('voterName');
        $select['ap.municipality_name'] = $request->get('municipalityName');
        $select['ap.barangay_name'] = $request->get('barangayName');
        $select['ap.contact_no'] = $request->get('contactNo');
        $select['h.pro_voter_id'] = $request->get('proVoterId');

        foreach ($select as $key => $value) {
            $searchValue = $select[$key];
            if ($searchValue != null || !empty($searchValue)) {
                if ($key == 'h.pro_voter_id') {
                    $sWhere .= " AND " . $key . "= '" . $searchValue . "' ";
                } else {
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

        $sql = "SELECT COALESCE(count(h.id),0) 
                FROM tbl_organization_hierarchy h 
                INNER JOIN tbl_attendance_detail a
                ON h.pro_voter_id = a.pro_voter_id 
                LEFT JOIN tbl_attendance_profile ap 
                ON ap.hdr_id =  a.id 
                WHERE 1 ";

        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(ap.id),0) 
                FROM tbl_organization_hierarchy h 
                INNER JOIN tbl_attendance_detail a
                ON h.pro_voter_id = a.pro_voter_id 
                LEFT JOIN tbl_attendance_profile ap 
                ON ap.hdr_id =  a.id
                WHERE 1 ";

        $sql .= $sWhere . " " . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();


        $sql = "SELECT ap.* FROM tbl_organization_hierarchy h 
                INNER JOIN tbl_attendance_detail a
                ON h.pro_voter_id = a.pro_voter_id 
                LEFT JOIN tbl_attendance_profile ap 
                ON ap.hdr_id =  a.id
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
     * @Route("/ajax_get_voterslist_json", 
     * name="ajax_get_voterslist_json", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxGetVoterslistJson(Request $request)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");
        $sql = "SELECT  * FROM tbl_project_voter 
        WHERE elect_id = 423 
        AND is_non_voter <> 1 
        AND municipality_no = '16'
        ORDER BY municipality_name, barangay_name, voter_name";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        $data = $stmt->fetchAll();

        if (count($data) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($data);
    }
}
