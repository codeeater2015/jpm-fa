<?php
namespace AppBundle\Controller;

use AppBundle\Entity\ProjectVoter;
use AppBundle\Entity\HouseholdHeader;
use AppBundle\Entity\HouseholdDetail;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @Route("/household")
 */

class HouseholdController extends Controller
{
    const STATUS_ACTIVE = 'A';
    const STATUS_INACTIVE = 'I';
    const STATUS_BLOCKED = 'B';
    const STATUS_PENDING = 'PEN';
    const MODULE_MAIN = "VOTER";

    /**
     * @Route("", name="household_index", options={"main" = true })
     */

    public function indexAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');
        $imgUrl = $this->getParameter('img_url');

        return $this->render('template/household/index.html.twig', ['user' => $user, "hostIp" => $hostIp, 'imgUrl' => $imgUrl]);
    }

    /**
     * @Route("/printing", name="household_printing_index", options={"main" = true })
     */

    public function householdPrintingAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');
        $imgUrl = $this->getParameter('img_url');

        return $this->render('template/household-printing/index.html.twig', ['user' => $user, "hostIp" => $hostIp, 'imgUrl' => $imgUrl]);
    }

    /**
     * @Route("/monitoring", name="household_monitoring", options={"main" = true })
     */

    public function householdMonitoringAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');
        $imgUrl = $this->getParameter('img_url');

        return $this->render('template/household-monitoring/index.html.twig', ['user' => $user, "hostIp" => $hostIp, 'imgUrl' => $imgUrl]);
    }

    /**
     * @Route("/ajax_post_household_header", 
     * 	name="ajax_post_household_header",
     *	options={"expose" = true}
     * )
     * @Method("POST")
     */

    public function ajaxPostHouseholdHeaderAction(Request $request)
    {
        $user = $this->get("security.token_storage")->getToken()->getUser();
        $em = $this->getDoctrine()->getManager("electPrep2024");
        ;

        $householdNo = $this->getNewHouseholdNoByBarangay($request->get('municipalityNo'), $request->get('barangayNo'));

        $sql = "SELECT * FROM psw_barangay WHERE municipality_code = ? AND brgy_no = ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, 53 . $request->get('municipalityNo'));
        $stmt->bindValue(2, $request->get('barangayNo'));
        $stmt->execute();

        $barangay = $stmt->fetch(\PDO::FETCH_ASSOC);


        $entity = new HouseholdHeader();
        $entity->setElectId($request->get('electId'));
        $entity->setProVoterId($request->get('proVoterId'));
        $entity->setHouseholdNo($this->getNewHouseholdNo());
        $entity->setHouseholdCode($barangay['short_name'] . $householdNo);
        $entity->setMunicipalityNo($request->get('municipalityNo'));
        $entity->setBarangayNo($request->get('barangayNo'));

        $entity->setCreatedAt(new \DateTime());
        $entity->setCreatedBy($user->getUsername());
        $entity->setRemarks($request->get('remarks'));
        $entity->setStatus(self::STATUS_ACTIVE);

        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy(['proVoterId' => intval($request->get('proVoterId'))]);

        if ($proVoter) {
            if (!empty($request->get('cellphoneNo')))
                $proVoter->setCellphone($request->get('cellphoneNo'));

            $proVoter->setBirthdate(trim($request->get('birthdate')));
            $proVoter->setVoterGroup(trim(strtoupper($request->get('voterGroup'))));
            $proVoter->setPosition('HLEADER');


            $entity->setVoterName($proVoter->getVoterName());
            $entity->setProIdCode($proVoter->getProIdCode());
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

        $em->persist($entity);
        $em->flush();
        $em->clear();

        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($entity));
    }

    private function getNewHouseholdNo()
    {
        $householdNo = 1;

        $em = $this->getDoctrine()->getManager("electPrep2024");
        ;

        $sql = "SELECT household_no FROM tbl_household_hdr ORDER BY household_no DESC LIMIT 1 ";

        $stmt = $em->getConnection()->query($sql);

        $request = $stmt->fetch();

        if ($request) {
            $householdNo = intval($request['household_no']) + 1;
        }

        return $householdNo;
    }

    /**
     * @Route("/ajax_patch_household_header/{householdId}", 
     * 	name="ajax_patch_household_header",
     *	options={"expose" = true}
     * )
     * @Method("PATCH")
     */

    public function ajaxPatchHouseholdHeaderAction(Request $request, $householdId)
    {
        $user = $this->get("security.token_storage")->getToken()->getUser();
        $em = $this->getDoctrine()->getManager("electPrep2024");

        $entity = $em->getRepository("AppBundle:HouseholdHeader")
            ->find($householdId);

        if (!$entity)
            return new JsonResponse([], 404);

        $entity->setProVoterId($request->get('proVoterId'));
        $entity->setMunicipalityNo($request->get('municipalityNo'));
        $entity->setBarangayNo($request->get('barangayNo'));
        $entity->setVoterName($request->get('voterName'));

        $entity->setContactNo($request->get('cellphoneNo'));

        $validator = $this->get('validator');
        $violations = $validator->validate($entity, [], 'edit');

        $errors = [];

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse($errors, 400);
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


        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy(['proVoterId' => intval($request->get('proVoterId'))]);

        if ($proVoter) {
            if (!empty($request->get('cellphoneNo')))
                $proVoter->setCellphone($request->get('cellphoneNo'));

            $proVoter->setBirthdate(trim($request->get('birthdate')));
            $proVoter->setPosition('HLEADER');

            $entity->setVoterName($proVoter->getVoterName());
            $entity->setProIdCode($proVoter->getProIdCode());
        }

        $em->flush();
        $em->clear();

        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($entity));
    }


    /**
     * @Route("/ajax_get_datatable_household_header", name="ajax_get_datatable_household_header", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */

    public function ajaxGetDatatableHouseholdHeaderAction(Request $request)
    {
        $columns = array(
            0 => "h.household_code",
            1 => "h.voter_name",
            2 => "h.voter_name",
            3 => "h.municipality_name",
            4 => "h.barangay_name",
            5 => "h.household_code",
        );

        $sWhere = "";

        $select['h.voter_name'] = $request->get("voterName");
        $select['h.municipality_name'] = $request->get("municipalityName");
        $select['h.barangay_name'] = $request->get("barangayName");
        $select['h.elect_id'] = $request->get("electId");
        $select['h.municipality_no'] = $request->get("municipalityNo");
        $select['h.barangay_no'] = $request->get("barangayNo");
        $select['h.household_code'] = $request->get("householdCode");
        $select['pv.voter_group'] = $request->get("voterGroup");

        foreach ($select as $key => $value) {
            $searchValue = $select[$key];
            if ($searchValue != null || !empty($searchValue)) {

                if ($key == 'h.elect_id' || $key == 'h.municipality_no' || $key == 'h.barangay_no') {
                    $sWhere .= "AND  {$key} = '{$searchValue}' ";
                }
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

        $sql = "SELECT COALESCE(count(h.id),0) FROM tbl_household_hdr h ";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.id),0) FROM tbl_household_hdr h 
                INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = h.pro_voter_id 
                WHERE 1 ";

        $sql .= $sWhere . ' ' . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT h.*,pv.is_non_voter, pv.voter_group FROM tbl_household_hdr h 
            INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = h.pro_voter_id 
            WHERE 1 " . $sWhere . ' ' . $sOrder . " LIMIT {$length} OFFSET {$start} ";

        $stmt = $em->getConnection()->query($sql);
        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        foreach ($data as &$row) {
            $sql = "SELECT COUNT(pv.pro_voter_id)  as total_members,
                    COALESCE(COUNT(CASE WHEN pv.is_non_voter = 0 then 1 end),0) as total_voters,
                    COALESCE(COUNT(CASE WHEN pv.is_non_voter = 1 then 1 end),0) as total_non_voters

                    FROM tbl_household_dtl hd 
                    INNER JOIN tbl_project_voter pv 
                    ON pv.pro_voter_id = hd.pro_voter_id 
                    WHERE hd.household_id = ? ";
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $row['id']);
            $stmt->execute();

            $summary = $stmt->fetch(\PDO::FETCH_ASSOC);


            $row['total_members'] = $summary['total_members'] + 1;
            $row['total_voters'] = $row['is_non_voter'] != 1 ? $summary['total_voters'] + 1 : $summary['total_voters'];
            $row['total_non_voters'] = $row['is_non_voter'] == 1 ? $summary['total_non_voters'] + 1 : $summary['total_non_voters'];
        }

        $draw = (null !== $request->query->get('draw')) ? $request->query->get('draw') : 0;
        $res['data'] = $data;
        $res['recordsTotal'] = $recordsTotal;
        $res['recordsFiltered'] = $recordsFiltered;
        $res['draw'] = $draw;

        return new JsonResponse($res);
    }



    /**
     * @Route("/ajax_get_datatable_household_header_no_recruitment", name="ajax_get_datatable_household_header_no_recruitment", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */

    public function ajaxGetDatatableHouseholdHeaderNoRecruitmentAction(Request $request)
    {
        $columns = array(
            0 => "h.household_code",
            1 => "h.voter_name",
            2 => "h.municipality_name",
            3 => "h.barangay_name"
        );

        $sWhere = "";

        $select['h.voter_name'] = $request->get("voterName");
        $select['h.municipality_name'] = $request->get("municipalityName");
        $select['h.barangay_name'] = $request->get("barangayName");
        $select['h.elect_id'] = $request->get("electId");
        $select['h.municipality_no'] = $request->get("municipalityNo");
        $select['h.barangay_no'] = $request->get("barangayNo");

        foreach ($select as $key => $value) {
            $searchValue = $select[$key];
            if ($searchValue != null || !empty($searchValue)) {

                if ($key == 'h.elect_id' || $key == 'h.municipality_no' || $key == 'h.barangay_no') {
                    $sWhere .= "AND  {$key} = '{$searchValue}' ";
                }
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
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(h.id),0) FROM tbl_household_hdr h WHERE h.pro_id_code NOT IN (SELECT r.pro_id_code FROM tbl_recruitment_hdr r ) ";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.id),0) FROM tbl_household_hdr h WHERE h.pro_id_code NOT IN (SELECT r.pro_id_code FROM tbl_recruitment_hdr r ) ";

        $sql .= $sWhere . ' ' . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT h.* FROM tbl_household_hdr h 
            WHERE h.pro_id_code NOT IN (SELECT r.pro_id_code FROM tbl_recruitment_hdr r ) " . $sWhere . ' ' . $sOrder . " LIMIT {$length} OFFSET {$start} ";

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
     * @Route("/ajax_delete_household_header/{householdId}", 
     * 	name="ajax_delete_household_header",
     *	options={"expose" = true}
     * )
     * @Method("DELETE")
     */

    public function ajaxDeleteHouseholdHeaderAction($householdId)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");
        ;
        $entity = $em->getRepository("AppBundle:HouseholdHeader")->find($householdId);

        if (!$entity)
            return new JsonResponse(null, 404);

        $entities = $em->getRepository('AppBundle:HouseholdDetail')->findBy([
            'householdId' => $entity->getId()
        ]);

        foreach ($entities as $detail) {
            $em->remove($detail);
        }

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null, 200);
    }

    /**
     * @Route("/ajax_get_household_header/{id}",
     *       name="ajax_get_household_header",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetHouseholdHeader($id)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");
        ;
        $entity = $em->getRepository("AppBundle:HouseholdHeader")
            ->find($id);

        if (!$entity) {
            return new JsonResponse(['message' => 'not found']);
        }

        $serializer = $this->get("serializer");
        $entity = $serializer->normalize($entity);

        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->find($entity['proVoterId']);

        if ($proVoter != null) {
            $entity['cellphone'] = $proVoter->getCellphone();
            $entity['lgc'] = $this->getLGC($proVoter->getMunicipalityNo(), $proVoter->getBrgyNo());
        } else {
            $entity['cellphone'] = "VOTER MISSING";
            $entity['lgc'] = [
                "voter_name" => "VOTER MISSING",
                "cellphone" => "VOTER MISSING"
            ];
        }

        return new JsonResponse($entity);
    }


    private function getLGC($municipalityNo, $barangayNo)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");
        ;
        $sql = "SELECT pv.voter_name, pv.cellphone, la.municipality_name, la.barangay_name FROM tbl_location_assignment la INNER JOIN tbl_project_voter pv 
                ON pv.pro_voter_id = la.pro_voter_id  
                WHERE la.municipality_no = ? AND la.barangay_no = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipalityNo);
        $stmt->bindValue(2, $barangayNo);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row == null ? ['voter_name' => "No LGC"] : $row;
    }


    /**
     * @Route("/ajax_get_household_header_full/{id}",
     *       name="ajax_get_household_header_full",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetHouseholdFullHeader($id)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");
        ;

        $sql = "SELECT h.*, pv.cellphone, pv.pro_voter_id, pv.birthdate, pv.gender,
                pv.firstname, pv.middlename, pv.lastname, pv.ext_name, pv.civil_status, pv.bloodtype,
                pv.occupation, pv.religion, pv.dialect, pv.ip_group
                FROM tbl_household_hdr h 
                INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = h.pro_voter_id 
                WHERE h.id = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $id);
        $stmt->execute();

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_post_household_detail", 
     * 	name="ajax_post_household_detail",
     *	options={"expose" = true}
     * )
     * @Method("POST")
     */

    public function ajaxPostHouseholdDetailAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");
        ;
        $user = $this->get("security.token_storage")->getToken()->getUser();

        $hdr = $em->getRepository("AppBundle:HouseholdHeader")
        ->find($request->get('householdId'));

        if(!$hdr)
            return new JsonResponse(['message' => 'household not found...'], 404);

        $entity = new HouseholdDetail();
        $entity->setHouseholdId($request->get('householdId'));
        $entity->setProVoterId($request->get('proVoterId'));
        $entity->setRelationship(trim(strtoupper($request->get('relationship'))));
        $entity->setGender($request->get('gender'));
        $entity->setBirthDate($request->get('birthdate'));
        $entity->setFirstname(trim(strtoupper($request->get('firstname'))));
        $entity->setMiddlename(trim(strtoupper($request->get('middlename'))));
        $entity->setLastname(trim(strtoupper($request->get('lastname'))));
        $entity->setExtName(trim(strtoupper($request->get('extName'))));
        $entity->setMunicipalityNo($request->get('municipalityNo'));
        $entity->setBarangayNo($request->get('barangayNo'));
        $entity->setCellphone(trim($request->get('cellphone')));
        $entity->setPosition(trim(strtoupper($request->get('position'))));

        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy(['proVoterId' => intval($request->get('proVoterId'))]);

        if ($proVoter) {
            $entity->setVoterName($proVoter->getVoterName());
            $entity->setProIdCode($proVoter->getProIdCode());
        }

        $entity->setCreatedAt(new \DateTime());
        $entity->setCreatedBy($user->getUsername());
        $entity->setRemarks($request->get('remarks'));
        $entity->setStatus(self::STATUS_ACTIVE);

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

            $proVoter->setGender($entity->getGender());
            $proVoter->setBirthdate($entity->getBirthdate());
            $proVoter->setPosition('HMEMBER');
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
          
        $proVoter->setAsnMunicipalityName($hdr->getMunicipalityName());
        $proVoter->setAsnMunicipalityNo($hdr->getMunicipalityNo());
        $proVoter->setAsnBarangayName($hdr->getBarangayName());
        $proVoter->setAsnBarangayNo($hdr->getBarangayNo());

        $em->persist($entity);
        $em->flush();
        $em->clear();

        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($entity));
    }

    /**
     * @Route("/ajax_select2_relationship",
     *       name="ajax_select2_relationship",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2Relationship(Request $request)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT relationship FROM tbl_household_dtl h WHERE h.relationship LIKE ? ORDER BY h.relationship ASC LIMIT 30";
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
     * @Route("/ajax_get_datatable_household_detail", name="ajax_get_datatable_household_detail", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */

    public function ajaxGetDatatableHouseholdDetailAction(Request $request)
    {
        $columns = array(
            0 => "h.household_id",
            1 => "h.voter_name",
            2 => "h.relationship",
            3 => "h.barangay_name",
            4 => "h.cellphone"
        );

        $sWhere = "";

        $select['h.household_id'] = $request->get('householdCode');
        $select['h.voter_name'] = $request->get("voterName");
        $householdId = $request->get('householdId');

        foreach ($select as $key => $value) {
            $searchValue = $select[$key];
            if ($searchValue != null || !empty($searchValue)) {
                $sWhere .= " AND " . $key . " LIKE '%" . $searchValue . "%'";
            }
        }


        $sWhere .= " AND h.household_id = ${householdId} ";

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

        $sql = "SELECT COALESCE(count(h.id),0) FROM tbl_household_dtl h WHERE h.household_id = ${householdId}";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.id),0) FROM tbl_household_dtl h WHERE 1 ";

        $sql .= $sWhere . ' ' . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT h.*, v.birthdate , v.cellphone,v.is_non_voter FROM tbl_household_dtl h INNER JOIN tbl_project_voter v ON v.pro_voter_id = h.pro_voter_id 
            WHERE 1 " . $sWhere . ' ' . $sOrder . " LIMIT {$length} OFFSET {$start} ";

        $stmt = $em->getConnection()->query($sql);
        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['total_members'] = 0;
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
     * @Route("/ajax_delete_household_detail/{householdDetailId}", 
     * 	name="ajax_delete_household_detail",
     *	options={"expose" = true}
     * )
     * @Method("DELETE")
     */

    public function ajaxDeleteHouseholdDetailAction($householdDetailId)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");
        ;
        $entity = $em->getRepository("AppBundle:HouseholdDetail")->find($householdDetailId);

        if (!$entity)
            return new JsonResponse(null, 404);

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null, 200);
    }

    /**
     * @Route("/ajax_fill_household_summary", 
     * 	name="ajax_fill_household_summary",
     *	options={"expose" = true}
     * )
     * @Method("GET")
     */

    public function ajaxFillHouseholdSummaryAction(Request $request)
    {
        $user = $this->get("security.token_storage")->getToken()->getUser();

        $em = $this->getDoctrine()->getManager("electPrep2024");
        ;

        $sql = "SELECT * FROM psw_municipality 
                WHERE province_code = 53 AND municipality_no <> 16  AND municipality_no IN (SELECT DISTINCT municipality_no FROM tbl_project_voter pv 
                WHERE pv.elect_id = 3 AND pv.pro_id = 3 AND voter_group IN ('LOPP','LPPP','LGO','LGC')) ORDER BY NAME ASC";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, 53);
        $stmt->execute();

        $municipalities = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $data = [];

        foreach ($municipalities as $municipality) {

            $sql = "DELETE FROM tbl_household_summary WHERE municipality_no = ? ";
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $municipality['municipality_no']);
            $stmt->execute();

            $sql = "SELECT * FROM psw_barangay 
                    WHERE municipality_code = ? ORDER BY name ASC";
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $municipality['municipality_code']);
            $stmt->execute();

            $barangays = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $electId = 3;
            $proId = 3;

            foreach ($barangays as $barangay) {
                $totalHousehold = 0;
                $totalMembers = 0;
                $totalDuplicates = 0;
                $totalVoter = 0;
                $totalNonVoter = 0;

                $sql = "SELECT COALESCE(COUNT(hh.id),0) as total_household 
                        FROM tbl_household_hdr hh 
                        WHERE hh.elect_id = ? 
                        AND hh.municipality_no = ? 
                        AND hh.barangay_no = ? ";

                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $electId);
                $stmt->bindValue(2, $municipality['municipality_no']);
                $stmt->bindValue(3, $barangay['brgy_no']);
                $stmt->execute();

                $totalHousehold = $stmt->fetch(\PDO::FETCH_ASSOC)['total_household'];


                $sql = "SELECT COALESCE(COUNT(DISTINCT hd.pro_voter_id),0) AS total_member 
                        FROM tbl_household_dtl hd 
                        INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = hd.pro_voter_id 
                        WHERE pv.elect_id = ? AND pv.municipality_no = ? AND pv.brgy_no = ? ";

                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $electId);
                $stmt->bindValue(2, $municipality['municipality_no']);
                $stmt->bindValue(3, $barangay['brgy_no']);
                $stmt->execute();

                $totalMembers = $stmt->fetch(\PDO::FETCH_ASSOC)['total_member'];

                $sql = "SELECT COUNT(pv.pro_voter_id) AS exists_count 
                FROM tbl_household_dtl hd INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = hd.pro_voter_id 
                WHERE pv.elect_id = ? AND pv.municipality_no = ? AND pv.brgy_no = ? 
                GROUP BY pv.pro_voter_id
                HAVING exists_count > 1";

                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $electId);
                $stmt->bindValue(2, $municipality['municipality_no']);
                $stmt->bindValue(3, $barangay['brgy_no']);
                $stmt->execute();

                $totalDuplicates = count($stmt->fetchAll(\PDO::FETCH_ASSOC));

                $sql = "SELECT COUNT(pv.pro_voter_id) AS exists_count 
                FROM tbl_household_dtl hd INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = hd.pro_voter_id 
                WHERE pv.elect_id = ? AND pv.municipality_no = ? 
                GROUP BY pv.pro_voter_id
                HAVING exists_count > 1 
                ORDER BY exists_count DESC
                LIMIT 1";

                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $electId);
                $stmt->bindValue(2, $municipality['municipality_no']);
                $stmt->execute();

                $maxDuplicate = $stmt->fetch(\PDO::FETCH_ASSOC)['exists_count'];

                $sql = "SELECT COALESCE(count(DISTINCT pv.pro_voter_id),0) AS total_count
                        FROM tbl_household_dtl hd 
                        INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = hd.pro_voter_id
                        WHERE pv.is_non_voter = 1 AND pv.elect_id = ? AND pv.municipality_no = ? AND pv.brgy_no = ?  ";

                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $electId);
                $stmt->bindValue(2, $municipality['municipality_no']);
                $stmt->bindValue(3, $barangay['brgy_no']);
                $stmt->execute();

                $totalMemberNonVoter = intval($stmt->fetch(\PDO::FETCH_ASSOC)['total_count']);

                $sql = "SELECT COALESCE(count(DISTINCT pv.pro_voter_id),0) AS total_count
                        FROM tbl_household_dtl hd 
                        INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = hd.pro_voter_id
                        WHERE pv.is_non_voter <> 1 AND pv.elect_id = ? AND pv.municipality_no = ? AND pv.brgy_no = ? ";

                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $electId);
                $stmt->bindValue(2, $municipality['municipality_no']);
                $stmt->bindValue(3, $barangay['brgy_no']);
                $stmt->execute();

                $totalMemberVoter = intval($stmt->fetch(\PDO::FETCH_ASSOC)['total_count']);

                $sql = "SELECT COALESCE(count(DISTINCT pv.pro_voter_id),0) AS total_count
                FROM tbl_household_hdr hh 
                INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = hh.pro_voter_id
                WHERE pv.is_non_voter = 1 AND pv.elect_id = ? AND pv.municipality_no = ? AND pv.brgy_no = ? ";

                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $electId);
                $stmt->bindValue(2, $municipality['municipality_no']);
                $stmt->bindValue(3, $barangay['brgy_no']);
                $stmt->execute();

                $totalLeaderNonVoter = intval($stmt->fetch(\PDO::FETCH_ASSOC)['total_count']);


                $sql = "SELECT COALESCE(count(DISTINCT pv.pro_voter_id),0) AS total_count
                FROM tbl_household_hdr hh 
                INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = hh.pro_voter_id
                WHERE pv.is_non_voter <> 1 AND pv.elect_id = ? AND pv.municipality_no = ? AND pv.brgy_no = ? ";

                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $electId);
                $stmt->bindValue(2, $municipality['municipality_no']);
                $stmt->bindValue(3, $barangay['brgy_no']);
                $stmt->execute();

                $totalLeaderVoter = intval($stmt->fetch(\PDO::FETCH_ASSOC)['total_count']);

                $sql = "INSERT INTO tbl_household_summary(
                    elect_id,pro_id,municipality_no,municipality_name,
                    barangay_no, barangay_name, total_household, total_members,
                    total_duplicates, max_duplicate_count, total_member_voter,
                    total_member_non_voter, total_leader_voter, total_leader_non_voter
                )
                VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)
                ";

                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $electId);
                $stmt->bindValue(2, $proId);
                $stmt->bindValue(3, $municipality['municipality_no']);
                $stmt->bindValue(4, $municipality['name']);
                $stmt->bindValue(5, $barangay['brgy_no']);
                $stmt->bindValue(6, $barangay['name']);
                $stmt->bindValue(7, $totalHousehold);
                $stmt->bindValue(8, $totalMembers);
                $stmt->bindValue(9, $totalDuplicates);
                $stmt->bindValue(10, $maxDuplicate);
                $stmt->bindValue(11, $totalMemberVoter);
                $stmt->bindValue(12, $totalMemberNonVoter);
                $stmt->bindValue(13, $totalLeaderVoter);
                $stmt->bindValue(14, $totalLeaderNonVoter);
                $stmt->execute();

                // $stmt->bindValue(8, $totalMembers);
                // $stmt->bindValue(9, $totalDuplicates);
                // $stmt->bindValue(10, $maxDuplicate);
                // $stmt->bindValue(11, $totalMemberVoter);
                // $stmt->bindValue(12, $totalMemberNonVoter);
                // $stmt->bindValue(13, $totalLeaderVoter);
                // $stmt->bindValue(14, $totalLeaderNonVoter);

                $data[] = [
                    'municipality_name' => $municipality['name'],
                    'barangay_name' => $barangay['name'],
                    'total_household' => $totalHousehold,
                    'total_members' => $totalMembers,
                    'total_duplicates' => $totalDuplicates,
                    'max_duplicate' => $maxDuplicate,
                    'total_member_non_voter' => $totalMemberNonVoter,
                    'total_member_voter' => $totalMemberVoter,
                    'total_leader_non_voter' => $totalLeaderNonVoter,
                    'total_leader_voter' => $totalLeaderVoter
                ];

            }
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_household_get_transfer_profiles",
     *       name="ajax_household_get_transfer_profiles",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxTransferProfiles(Request $request)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");

        $sql = "SELECT 
                 a.municipality_name, a.barangay_name,  a.municipality_no, a.barangay_no , 
                 ad.id AS detail_id, ad.pro_voter_id, ad.pro_id_code, ad.contact_no, ad.voter_name,
                 COUNT(*) AS total_profile_members 
                 FROM tbl_attendance_detail ad 
                 INNER JOIN tbl_attendance_profile p 
                 ON ad.id = p.hdr_id 
                 INNER JOIN tbl_attendance a 
                 ON a.id = ad.hdr_id 
                 GROUP BY ad.pro_voter_id ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        $hdrs = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $hdrs[] = $row;
        }

        foreach ($hdrs as $hdr) {

            $leader = new HouseholdHeader();
            $leader->setMunicipalityName($hdr['municipality_name']);
            $leader->setMunicipalityNo($hdr['municipality_no']);
            $leader->setBarangayName($hdr['barangay_name']);
            $leader->setBarangayNo($hdr['barangay_no']);
            $leader->setProVoterId($hdr['pro_voter_id']);
            $leader->setVoterName($hdr['voter_name']);
            $leader->setContactNo($hdr['contact_no']);
            $leader->setElectId(423);


            $sql = "SELECT * FROM tbl_attendance_profile p WHERE hdr_id = ?";

            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $hdr['detail_id']);
            $stmt->execute();

            $em->persist($leader);
            $em->flush();

            $details = [];
            $details = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($details as $detail) {
                $member = new HouseholdDetail();
                $member->setHouseholdId($leader->getId());
                $member->setProVoterId($detail['pro_voter_id']);
                $member->setVoterName($detail['voter_name']);
                $member->setMunicipalityName($leader->getMunicipalityName());
                $member->setMunicipalityNo($leader->getMunicipalityNo());
                $member->setBarangayName($leader->getBarangayName());
                $member->setBarangayNo($leader->getBarangayNo());

                $em->persist($member);
                $em->flush();
            }
        }

        return new JsonResponse($hdrs, 200);
    }



    /**
     * @Route("/ajax_household_generate_household_no",
     *       name="ajax_household_generate_household_no",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGenerateHouseholdNo(Request $request)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");

        $sql = "SELECT * FROM tbl_household_hdr ORDER BY municipality_name, barangay_name ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        $hdrs = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $hdrs[] = $row;
        }

        $barangay = '';


        foreach ($hdrs as $hdr) {
            $householdNo = $this->getNewHouseholdNoByBarangay($hdr['municipality_no'], $hdr['barangay_no']);

            $sql = "SELECT * FROM psw_barangay WHERE municipality_code = ? AND brgy_no = ?";
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, 53 . $hdr['municipality_no']);
            $stmt->bindValue(2, $hdr['barangay_no']);
            $stmt->execute();

            $barangay = $stmt->fetch(\PDO::FETCH_ASSOC);


            $sql = "UPDATE tbl_household_hdr SET household_no = ? , household_code = ? WHERE id = ? ";

            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $householdNo);
            $stmt->bindValue(2, $barangay['short_name'] . $householdNo);
            $stmt->bindValue(3, $hdr['id']);

            $stmt->execute();
        }

        return new JsonResponse($hdrs);
    }

    private function getNewHouseholdNoByBarangay($municipalityNo, $barangayNo)
    {
        $householdNo = 1;

        $em = $this->getDoctrine()->getManager("electPrep2024");
        ;

        $sql = "SELECT household_no FROM tbl_household_hdr where municipality_no = ? AND barangay_no = ? 
               ORDER BY household_no DESC LIMIT 1 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipalityNo);
        $stmt->bindValue(2, $barangayNo);
        $stmt->execute();

        $request = $stmt->fetch();

        if ($request) {
            $householdNo = intval($request['household_no']) + 1;
        }

        return $householdNo;
    }

    /**
     * @Route("/ajax_patch_household_notes/{householdId}", 
     * 	name="ajax_patch_household_notes",
     *	options={"expose" = true}
     * )
     * @Method("PATCH")
     */

    public function ajaxPatchHouseholdNotesAction(Request $request, $householdId)
    {
        $user = $this->get("security.token_storage")->getToken()->getUser();
        $em = $this->getDoctrine()->getManager("electPrep2024");

        $entity = $em->getRepository("AppBundle:HouseholdHeader")
            ->find($householdId);

        if (!$entity)
            return new JsonResponse([], 404);

        $voter = $em->getRepository("AppBundle:ProjectVoter")
            ->find($entity->getProVoterId());


        if (!$voter)
            return new JsonResponse([], 404);

        $entity->setContactNo($request->get('contactNo'));
        $entity->setRemarks($request->get('remarks'));
        $entity->setUpdatedAt(new \DateTime());
        $entity->setUpdatedBy($user->getUsername());

        $voter->setCellphone($request->get('contactNo'));

        $em->flush();
        $em->clear();

        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($entity));
    }



    /**
     * @Route("/ajax_get_table_household_headers", name="ajax_get_table_household_headers", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */

    public function ajaxGetTableHouseholdHeadersAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");

        $municipalityName = $request->get("municipalityName");
        $barangayName = $request->get("barangayName");


        if (!$municipalityName || !$barangayName) {
            return new JsonResponse([], 404);
        }

        $sql = "SELECT h.*,pv.is_non_voter, pv.voter_group FROM tbl_household_hdr h 
             INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = h.pro_voter_id 
             WHERE h.municipality_no = ? AND h.barangay_no = ? ORDER BY municipality_name, barangay_name, voter_name  ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipalityName);
        $stmt->bindValue(2, $barangayName);
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        foreach ($data as &$row) {
            $sql = "SELECT COUNT(pv.pro_voter_id)  as total_members,
                     COALESCE(COUNT(CASE WHEN pv.is_non_voter = 0 then 1 end),0) as total_voters,
                     COALESCE(COUNT(CASE WHEN pv.is_non_voter = 1 then 1 end),0) as total_non_voters
 
                     FROM tbl_household_dtl hd 
                     INNER JOIN tbl_project_voter pv 
                     ON pv.pro_voter_id = hd.pro_voter_id 
                     WHERE hd.household_id = ? ORDER BY pv.voter_name ";
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $row['id']);
            $stmt->execute();

            $summary = $stmt->fetch(\PDO::FETCH_ASSOC);


            $row['total_members'] = $summary['total_members'] + 1;
            $row['total_voters'] = $row['is_non_voter'] != 1 ? $summary['total_voters'] + 1 : $summary['total_voters'];
            $row['total_non_voters'] = $row['is_non_voter'] == 1 ? $summary['total_non_voters'] + 1 : $summary['total_non_voters'];
        }


        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_get_table_household_monitoring_by_barangay", name="ajax_get_table_household_monitoring_by_barangay", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */

    public function ajaxGetTableHouseholdMonitoringByBarangayAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");

        $sql = "Select municipality_name, barangay_name ,DATE(updated_at) AS call_date, count(*) as total_household
         from tbl_household_hdr 
         wherE updated_at is not null 
         group by barangay_name
         ORDER BY municipality_name,barangay_name ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_get_table_household_monitoring_by_date", name="ajax_get_table_household_monitoring_by_date", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */

    public function ajaxGetTableHouseholdMonitoringByDateAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");

        $sql = "Select municipality_name, barangay_name ,DATE(updated_at) AS call_date, count(*) as total_household
         from tbl_household_hdr 
         wherE updated_at is not null 
         group by call_date
         ORDER BY call_date ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return new JsonResponse($data);
    }


     /**
     * @Route("/ajax_m_get_household_voters_summary",
     *       name="ajax_m_get_household_voters_summary",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

     public function ajaxGetHouseholdVotersSummary(Request $request)
     {
 
         $em = $this->getDoctrine()->getManager("electPrep2024");
 
         $sql = "SELECT asn_municipality_name, COUNT(*) AS total_voter,
                 COALESCE(COUNT(CASE WHEN pv.municipality_no = '01' THEN 1 END), 0) AS total_aborlan,
                 COALESCE(COUNT(CASE WHEN pv.municipality_no = '16' THEN 1 END), 0) AS total_puerto
                 FROM tbl_project_voter pv 
                 WHERE pv.position IN ('HLEADER','HMEMBER') 
                 AND pv.municipality_no IN ('01','16')
                 AND pv.is_non_voter = 0
                 GROUP BY pv.asn_municipality_name 
                 ORDER BY pv.asn_municipality_name";
 
         $stmt = $em->getConnection()->prepare($sql);
         $stmt->execute();
 
         $summary = [];
 
         $summary['voters'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
 
         if (!$summary)
             return new JsonResponse(['message' => 'Household not found. Please contact the system administrator'], 404);
 
 
         $sql = "SELECT asn_municipality_name, COUNT(*) AS total_voter,
                 COALESCE(COUNT(CASE WHEN pv.municipality_no = '01' THEN 1 END), 0) AS total_aborlan,
                 COALESCE(COUNT(CASE WHEN pv.municipality_no = '16' THEN 1 END), 0) AS total_puerto
                 FROM tbl_project_voter pv 
                 WHERE pv.position IN ('HLEADER','HMEMBER') 
                 and pv.municipality_no NOT IN ('01','16')
                 AND pv.is_non_voter = 0
                 GROUP BY pv.asn_municipality_name 
                 ORDER BY pv.asn_municipality_name";
 
         $stmt = $em->getConnection()->prepare($sql);
         $stmt->execute();
 
         $summary['total_voter_outside'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
 
         $sql = "SELECT pv.asn_municipality_name  , COALESCE(COUNT(DISTINCT pv.pro_voter_id ),0) AS total_voter_potential
                 FROM tbl_project_voter pv
                 WHERE pv.municipality_no IN ('01','16') 
                 AND pv.position IS NOT NULL AND pv.position <> ''
                 AND pv.is_non_voter = 1 
                 GROUP BY pv.asn_municipality_name 
                 ORDER BY pv.asn_municipality_name";
 
         $stmt = $em->getConnection()->prepare($sql);
         $stmt->execute();
 
         $summary['total_voter_potential'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
 
         $sql = "SELECT municipality_name , COUNT(DISTINCT pro_voter_id) AS total_household 
                 FROM tbl_household_hdr 
                 GROUP BY municipality_name
                 ORDER  BY municipality_name ";
 
         $stmt = $em->getConnection()->prepare($sql);
         $stmt->execute();
 
         $summary['household'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
 
         $sql = "SELECT asn_municipality_name, COUNT(*) AS total_voter,
                COALESCE(COUNT(CASE WHEN pv.voter_group = 'TOP LEADER' AND pv.position IN ('HLEADER','HMEMBER') THEN 1 END), 0) AS total_tl,
                COALESCE(COUNT(CASE WHEN pv.voter_group = 'K0' AND pv.position IN ('HLEADER','HMEMBER') THEN 1 END), 0) AS total_k0,
                COALESCE(COUNT(CASE WHEN pv.voter_group = 'K1' THEN 1 END), 0) AS total_k1,
                COALESCE(COUNT(CASE WHEN pv.voter_group = 'K2' THEN 1 END), 0) AS total_k2,
                COALESCE(COUNT(CASE WHEN (pv.voter_group = '' OR pv.voter_group IS NULL) AND  pv.position IN ('HLEADER') THEN 1 END), 0) AS total_no_pos,
                COALESCE(COUNT(CASE WHEN (pv.voter_group <> '' AND pv.voter_group IS NOT NULL) AND (pv.position IS NULL OR pv.position = '') THEN 1 END), 0) AS total_no_profile
                FROM tbl_project_voter pv 
                WHERE pv.elect_id = 423
                AND pv.municipality_no IN ('01','16')
                GROUP BY pv.asn_municipality_name 
                ORDER BY pv.asn_municipality_name";
                
         $stmt = $em->getConnection()->prepare($sql);
         $stmt->execute();
 
         $summary['hierarchy_summary'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
 
         return new JsonResponse($summary);
     }

    /**
     * @Route("/ajax_m_get_household_voters_summary_by_barangay/{municipalityNo}",
     *       name="ajax_m_get_household_voters_summary_by_barangay",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

     public function ajaxGetHouseholdVotersSummaryByBarangay(Request $request, $municipalityNo )
     {
         $em = $this->getDoctrine()->getManager("electPrep2024");
 
         $sql = "SELECT pv.asn_municipality_name, pv.asn_barangay_name, COUNT(*) AS total_voter,
                COALESCE(COUNT(CASE WHEN pv.municipality_no = '01' AND pv.is_non_voter = 0 THEN 1 END), 0) AS total_aborlan,
                COALESCE(COUNT(CASE WHEN pv.municipality_no = '16' AND pv.is_non_voter = 0 THEN 1 END), 0) AS total_puerto,
                COALESCE(COUNT(CASE WHEN pv.municipality_no NOT IN ('16','01') AND pv.is_non_voter = 0 THEN 1 END), 0) AS total_outside,
                COALESCE(COUNT(CASE WHEN pv.is_non_voter = 1 THEN 1 END), 0) AS total_potential,
                ( SELECT COALESCE(COUNT(hh.id),0) FROM tbl_household_hdr hh WHERE hh.municipality_no = pv.asn_municipality_no AND hh.barangay_no = pv.asn_barangay_no) AS total_household
                FROM tbl_project_voter pv 

                WHERE pv.position IN ('HLEADER','HMEMBER') 
                AND pv.asn_municipality_no  = ? 

                GROUP BY pv.asn_municipality_no,pv.asn_barangay_no
                ORDER BY asn_municipality_name, asn_barangay_name";
 
         $stmt = $em->getConnection()->prepare($sql);
         $stmt->bindValue(1, $municipalityNo);
         $stmt->execute();
 
         $summary = [];
         $summary = $stmt->fetchAll(\PDO::FETCH_ASSOC);
 
         return new JsonResponse($summary);
     }
}
