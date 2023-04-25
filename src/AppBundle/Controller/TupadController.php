<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\TupadTransaction;
use AppBundle\Entity\TupadTransactionDetail;
use AppBundle\Entity\ProjectVoter;

/**
 * @Route("/tupad")
 */

class TupadController extends Controller
{
    const STATUS_ACTIVE = 'A';
    const MODULE_MAIN = "TUPAD_COMPONENT";

    /**
     * @Route("", name="tupad_index", options={"main" = true})
     */

    public function indexAction(Request $request)
    {
        //$this->denyAccessUnlessGranted("entrance",self::MODULE_MAIN);
        $user = $this->get('security.token_storage')->getToken()->getUser();
        return $this->render('template/tupad/index.html.twig', ['user' => $user]);
    }

    /**
     * @Route("/summary", name="tupad_summary_index", options={"main" = true})
     */

    public function summaryAction(Request $request)
    {
        //$this->denyAccessUnlessGranted("entrance",self::MODULE_MAIN);
        $user = $this->get('security.token_storage')->getToken()->getUser();
        return $this->render('template/tupad/summary.html.twig', ['user' => $user]);
    }

    /**
     * @Route("/ajax_tupad_post_transaction", 
     * 	name="ajax_tupad_post_transaction",
     *	options={"expose" = true}
     * )
     * @Method("POST")
     */

    public function ajaxPostTupadTransactionAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager("tupad");

        $voter = $em->getRepository("AppBundle:ProjectVoter")
            ->findOneBy([
                'proId' => 3,
                'proVoterId' => $request->get("proVoterId"),
            ]);

        $entity = new TupadTransaction();
        $entity->setProVoterId($request->get("proVoterId"));
        $entity->setProIdCode($request->get('proIdCode'));
        $entity->setGeneratedIdNo($request->get('generatedIdNo'));
        $entity->setSourceMunicipality(strtoupper($request->get('sourceMunicipality')));
        $entity->setSourceBarangay(strtoupper($request->get('sourceBarangay')));
        $entity->setBMunicipality(strtoupper($request->get('bMunicipality')));
        $entity->setBBarangay(strtoupper($request->get('bBarangay')));
        $entity->setBName(strtoupper($request->get('bName')));
        $entity->setBExtname(strtoupper($request->get('bExtname')));
        $entity->setIsVoter(strtoupper($request->get('isVoter')));
        $entity->setServiceType($request->get('serviceType'));
        $entity->setSource($request->get('source'));
        $entity->setReleaseDate($request->get('releaseDate'));
        $entity->setCellphoneNo($request->get('cellphoneNo'));
        $entity->setBStatus($request->get('bStatus'));
        $entity->setRemarks(strtoupper($request->get('remarks')));
        $entity->setStatus(self::STATUS_ACTIVE);
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

        $em->persist($entity);
        $em->flush();

        if ($entity->getCellphoneNo() != "") {
            $voter->setCellphone($entity->getCellphoneNo());
            $em->flush();
        }

        $em->clear();
        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($entity));
    }

    /**
     * @Route("/ajax_tupad_patch_transaction/{hdrId}", 
     * 	name="ajax_tupad_patch_transaction",
     *	options={"expose" = true}
     * )
     * @Method("PATCH")
     */

    public function ajaxPatchTupadTransactionAction(Request $request, $hdrId)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager("tupad");

        $hdr = $em->getRepository("AppBundle:TupadTransaction")
            ->find($hdrId);

        if (!$hdr)
            return new JsonResponse(null, 404);

        $voter = $em->getRepository("AppBundle:ProjectVoter")
            ->findOneBy([
                'proId' => 3,
                'proVoterId' => $hdr->getProVoterId(),
            ]);
        

        $hdr->setCellphoneNo($request->get('cellphoneNo'));
     
        $validator = $this->get('validator');
        $violations = $validator->validate($hdr);

        $errors = [];

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse($errors, 400);
        }

        $em->flush();

        if ($hdr->getCellphoneNo() != "") {
            $voter->setCellphone($hdr->getCellphoneNo());
            $em->flush();
        }

        $em->clear();
        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($hdr));
    }

    /**
     * @Route("/ajax_tupad_post_transaction_detail/{hdrId}", 
     * 	name="ajax_tupad_post_transaction_detail",
     *	options={"expose" = true}
     * )
     * @Method("POST")
     */

    public function ajaxPostTupadTransactionDetailAction(Request $request, $hdrId)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager("tupad");

        $voter = $em->getRepository("AppBundle:ProjectVoter")
            ->findOneBy([
                'proId' => 3,
                'proVoterId' => $request->get("proVoterId"),
            ]);

        $entity = new TupadTransactionDetail();
        $entity->setHdrId($hdrId);
        $entity->setProVoterId($request->get("proVoterId"));
        $entity->setProIdCode($voter->getProIdCode());
        $entity->setGeneratedIdNo($voter->getGeneratedIdNo());
        $entity->setBMunicipality($voter->getMunicipalityName());
        $entity->setBBarangay($voter->getBarangayName());
        $entity->setBName($voter->getVoterName());
        $entity->setBExtname($voter->getExtname());
        $entity->setIsVoter(!$voter->getIsNonVoter());
        $entity->setBCellphoneNo($request->get("cellphoneNo"));
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

        $em->persist($entity);
        $em->flush();

        if ($entity->getBCellphoneNo() != "") {
            $voter->setCellphone($entity->getBCellphoneNo());
            $em->flush();
        }

        $em->clear();
        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($entity));
    }


    /**
     * @Route("/ajax_select2_tupad_project_voters", 
     *       name="ajax_select2_tupad_project_voters",
     *		options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2ProjectVoters(Request $request)
    {
        $em = $this->getDoctrine()->getManager("tupad");
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $electId = $request->get("electId");
        $provinceCode = $request->get("provinceCode");
        $municipalityNo = $request->get("municipalityNo");
        $brgyNo = $request->get("brgyNo");

        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT p.* FROM tbl_project_voter p 
                WHERE p.voter_name LIKE ? 
                AND p.province_code = ? 
                AND p.elect_id = ? 
                AND (municipality_no = ? OR ? IS NULL)
                AND (brgy_no = ? OR ? IS NULL)
                ORDER BY p.voter_name ASC LIMIT 10";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->bindValue(2, $provinceCode);
        $stmt->bindValue(3, $electId);
        $stmt->bindValue(4, $municipalityNo);
        $stmt->bindValue(5, empty($municipalityNo) ? null : $municipalityNo);
        $stmt->bindValue(6, $brgyNo);
        $stmt->bindValue(7, empty($brgyNo) ? null : $brgyNo);
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
     * @Route("/ajax_get_tupad_project_voter/{proId}/{proVoterId}",
     *       name="ajax_get_tupad_project_voter",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetProjectVoter($proId, $proVoterId)
    {
        $em = $this->getDoctrine()->getManager("tupad");
        $proVoter = $em->getRepository("AppBundle:ProjectVoter")
            ->findOneBy([
                'proId' => $proId,
                'proVoterId' => $proVoterId,
            ]);

        if (!$proVoter) {
            return new JsonResponse(['message' => 'not found']);
        }

        $serializer = $this->get("serializer");
        $proVoter = $serializer->normalize($proVoter);

        return new JsonResponse($proVoter);
    }


    /**
     * @Route("/ajax_post_tupad_temporary_voter",
     *     name="ajax_post_tupad_temporary_voter",
     *    options={"expose" = true}
     * )
     * @Method("POST")
     */

    public function ajaxPostProjectTemporaryVoterAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager("tupad");
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $entity = new ProjectVoter();
        $entity->setProId($request->get('proId'));
        $entity->setElectId($request->get('electId'));
        $entity->setFirstname(trim(strtoupper($request->get('firstname'))));
        $entity->setMiddlename(trim(strtoupper($request->get('middlename'))));
        $entity->setLastname(trim(strtoupper($request->get('lastname'))));
        $entity->setExtname(trim(strtoupper($request->get('extName'))));

        $voterName = $entity->getLastname() . ', ' . $entity->getFirstname() . ' ' . $entity->getMiddlename() . ' ' . $entity->getExtname();
        $entity->setVoterName(trim(strtoupper($voterName)));
        $entity->setGender($request->get('gender'));

        $entity->setIsNonVoter(1);
        $entity->setHasId(0);
        $entity->setHasPhoto(0);
        $entity->setIsKalaban(0);

        $entity->setProvinceCode(53);
        $entity->setMunicipalityNo($request->get('municipalityNo'));
        $entity->setBrgyNo($request->get('brgyNo'));

        $entity->setCreatedAt(new \DateTime());
        $entity->setCreatedBy($user->getUsername());
        $entity->setStatus(self::STATUS_ACTIVE);

        $validator = $this->get('validator');
        $violations = $validator->validate($entity, null, ['create']);

        $errors = [];

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse($errors, 400);
        }

        $em->persist($entity);
        $em->flush();

        $sql = "SELECT * FROM psw_municipality
        WHERE province_code = ?
        AND municipality_no = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, 53);
        $stmt->bindValue(2, $entity->getMunicipalityNo());
        $stmt->execute();

        $municipality = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($municipality != null) {
            $entity->setMunicipalityName($municipality['name']);
        }

        $sql = "SELECT * FROM psw_barangay
        WHERE brgy_code = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, 53 . $entity->getMunicipalityNo() . $entity->getBrgyNo());
        $stmt->execute();

        $barangay = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($barangay != null) {
            $entity->setBarangayName($barangay['name']);
        }

        $em->flush();
        $em->clear();

        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($entity));
    }

    /**
     * @Route("/ajax_get_datatable_tupad_transactions", name="ajax_get_datatable_tupad_transactions", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxGetDatatableTupadTransactionsAction(Request $request)
    {
        $columns = array(
            0 => "h.id",
            1 => "h.b_name",
            2 => "h.service_type",
            3 => "h.source_municipality",
            4 => "h.source_barangay",
            5 => "h.is_voter",
        );

        $sWhere = "";

        $select['h.b_name'] = $request->get('bName');
        $select['h.service_type'] = $request->get('serviceType');
        $select['h.source'] = $request->get('source');
        $select['h.release_date'] = $request->get('releaseDate');
        $select['h.source_municipality'] = $request->get('sourceMunicipality');
        $select['h.source_barangay'] = $request->get('sourceBarangay');
        $select['h.b_municipality'] = $request->get('bMunicipality');
        $select['h.b_barangay'] = $request->get('bBarangay');

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

        $em = $this->getDoctrine()->getManager("tupad");
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(h.id),0) FROM tbl_tupad_transaction h WHERE 1 ";

        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.id),0) FROM tbl_tupad_transaction h WHERE 1 ";

        $sql .= $sWhere . " " . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT h.* FROM tbl_tupad_transaction h 
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
     * @Route("/ajax_delete_tupad_transaction/{id}", 
     * 	name="ajax_delete_tupad_transaction",
     *	options={"expose" = true}
     * )
     * @Method("DELETE")
     */

    public function ajaxDeleteTupadAction($id)
    {
        $em = $this->getDoctrine()->getManager("tupad");
        $entity = $em->getRepository("AppBundle:TupadTransaction")->find($id);

        if (!$entity)
            return new JsonResponse(null, 404);

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null, 200);
    }


    /**
     * @Route("/ajax_delete_tupad_transaction_detail/{id}", 
     * 	name="ajax_delete_tupad_transaction_detail",
     *	options={"expose" = true}
     * )
     * @Method("DELETE")
     */

    public function ajaxDeleteTupadTransactionDetailAction($id)
    {
        $em = $this->getDoctrine()->getManager("tupad");
        $entity = $em->getRepository("AppBundle:TupadTransactionDetail")->find($id);

        if (!$entity)
            return new JsonResponse(null, 404);

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null, 200);
    }

    /**
     * @Route("/ajax_get_assistance_summary", name="ajax_get_assistance_summary", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxGetAssistanceSummaryAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager("tupad");
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = 'SELECT COUNT(*), source_municipality ,
        COALESCE(COUNT(CASE WHEN service_type = "SLP" THEN 1 END), 0) AS total_slp,
        (SELECT COUNT(DISTINCT s_trn.pro_voter_id) FROM tbl_tupad_transaction  s_trn WHERE s_trn.service_type = "SLP" AND s_trn.source_municipality = trn.source_municipality ) AS total_slp_uniq,
        (SELECT COUNT(DISTINCT ppv.pro_voter_id) FROM tbl_tupad_transaction  s_trn INNER JOIN tbl_project_voter ppv ON ppv.pro_voter_id  = s_trn.pro_voter_id  WHERE s_trn.service_type = "SLP" AND ppv.has_photo = 1 AND s_trn.source_municipality = trn.source_municipality ) AS total_slp_w_id,
        (SELECT COUNT(DISTINCT s_trn.pro_voter_id) FROM tbl_tupad_transaction  s_trn WHERE s_trn.service_type = "SLP" AND s_trn.is_voter = 1 AND s_trn.source_municipality = trn.source_municipality ) AS total_slp_is_voter,
        COALESCE(COUNT(CASE WHEN service_type = "AICS_FOOD" THEN 1 END), 0) AS total_aics_food,
        (SELECT COUNT(DISTINCT s_trn.pro_voter_id) FROM tbl_tupad_transaction  s_trn WHERE s_trn.service_type = "AICS_FOOD" AND s_trn.source_municipality = trn.source_municipality ) AS total_aics_food_uniq,
        (SELECT COUNT(DISTINCT ppv.pro_voter_id) FROM tbl_tupad_transaction  s_trn INNER JOIN tbl_project_voter ppv ON ppv.pro_voter_id = s_trn.pro_voter_id WHERE s_trn.service_type = "AICS_FOOD"  AND ppv.has_photo = 1 AND s_trn.source_municipality = trn.source_municipality ) AS total_aics_food_w_id,
        (SELECT COUNT(DISTINCT s_trn.pro_voter_id) FROM tbl_tupad_transaction  s_trn WHERE s_trn.service_type = "AICS_FOOD"  AND s_trn.is_voter = 1 AND s_trn.source_municipality = trn.source_municipality ) AS total_aics_food_is_voter,
        COALESCE(COUNT(CASE WHEN service_type = "AICS_EDUC" THEN 1 END), 0) AS total_aics_educ,
        (SELECT COUNT(DISTINCT s_trn.pro_voter_id) FROM tbl_tupad_transaction  s_trn WHERE s_trn.service_type = "AICS_EDUC" AND s_trn.source_municipality = trn.source_municipality ) AS total_aics_educ_uniq,
        (SELECT COUNT(DISTINCT ppv.pro_voter_id) FROM tbl_tupad_transaction  s_trn INNER JOIN tbl_project_voter ppv ON ppv.pro_voter_id = s_trn.pro_voter_id  WHERE s_trn.service_type = "AICS_EDUC" AND ppv.has_photo = 1 AND s_trn.source_municipality = trn.source_municipality ) AS total_aics_educ_w_id,
        (SELECT COUNT(DISTINCT s_trn.pro_voter_id) FROM tbl_tupad_transaction  s_trn WHERE s_trn.service_type = "AICS_EDUC" AND s_trn.is_voter = 1 AND s_trn.source_municipality = trn.source_municipality ) AS total_aics_educ_is_voter,
        COALESCE(COUNT(CASE WHEN service_type = "DISPLACED" THEN 1 END), 0) AS total_displaced,
        (SELECT COUNT(DISTINCT s_trn.pro_voter_id) FROM tbl_tupad_transaction  s_trn WHERE s_trn.service_type = "DISPLACED" AND s_trn.source_municipality = trn.source_municipality ) AS total_displaced_uniq,
        (SELECT COUNT(DISTINCT ppv.pro_voter_id) FROM tbl_tupad_transaction  s_trn INNER JOIN tbl_project_voter ppv ON ppv.pro_voter_id = s_trn.pro_voter_id WHERE s_trn.service_type = "DISPLACED" AND ppv.has_photo = 1 AND s_trn.source_municipality = trn.source_municipality ) AS total_displaced_w_id,
        (SELECT COUNT(DISTINCT s_trn.pro_voter_id) FROM tbl_tupad_transaction  s_trn WHERE s_trn.service_type = "DISPLACED" AND s_trn.is_voter = 1 AND s_trn.source_municipality = trn.source_municipality ) AS total_displaced_is_voter
        
        FROM tbl_tupad_transaction trn INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = trn.pro_voter_id
        
        WHERE (pv.is_kalaban <> 1 OR pv.is_kalaban IS NULL OR pv.is_kalaban = 0)
        GROUP BY source_municipality';

        $stmt = $em->getConnection()->query($sql);
        $data = [];


        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_get_assistance_municipality_summary/{municipalityName}", name="ajax_get_assistance_municipality_summary", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxGetMunicipalitySummaryAction(Request $request, $municipalityName)
    {
        $em = $this->getDoctrine()->getManager("tupad");
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = 'SELECT COUNT(*), source_barangay ,
        COALESCE(COUNT(CASE WHEN service_type = "SLP" THEN 1 END), 0) AS total_slp,
        (SELECT COUNT(DISTINCT s_trn.pro_voter_id) FROM tbl_tupad_transaction  s_trn WHERE s_trn.service_type = "SLP" AND s_trn.source_municipality = trn.source_municipality AND s_trn.source_barangay = trn.source_barangay ) AS total_slp_uniq,
        (SELECT COUNT(DISTINCT ppv.pro_voter_id) FROM tbl_tupad_transaction  s_trn INNER JOIN tbl_project_voter ppv ON ppv.pro_voter_id  = s_trn.pro_voter_id  WHERE s_trn.service_type = "SLP" AND ppv.has_photo = 1 AND s_trn.source_municipality = trn.source_municipality AND s_trn.source_barangay = trn.source_barangay ) AS total_slp_w_id,
        (SELECT COUNT(DISTINCT s_trn.pro_voter_id) FROM tbl_tupad_transaction  s_trn WHERE s_trn.service_type = "SLP" AND s_trn.is_voter = 1 AND s_trn.source_municipality = trn.source_municipality AND s_trn.source_barangay = trn.source_barangay ) AS total_slp_is_voter,
        COALESCE(COUNT(CASE WHEN service_type = "AICS_FOOD" THEN 1 END), 0) AS total_aics_food,
        (SELECT COUNT(DISTINCT s_trn.pro_voter_id) FROM tbl_tupad_transaction  s_trn WHERE s_trn.service_type = "AICS_FOOD" AND s_trn.source_municipality = trn.source_municipality AND s_trn.source_barangay = trn.source_barangay ) AS total_aics_food_uniq,
        (SELECT COUNT(DISTINCT ppv.pro_voter_id) FROM tbl_tupad_transaction  s_trn INNER JOIN tbl_project_voter ppv ON ppv.pro_voter_id = s_trn.pro_voter_id WHERE s_trn.service_type = "AICS_FOOD"  AND ppv.has_photo = 1 AND s_trn.source_municipality = trn.source_municipality AND s_trn.source_barangay = trn.source_barangay ) AS total_aics_food_w_id,
        (SELECT COUNT(DISTINCT s_trn.pro_voter_id) FROM tbl_tupad_transaction  s_trn WHERE s_trn.service_type = "AICS_FOOD"  AND s_trn.is_voter = 1 AND s_trn.source_municipality = trn.source_municipality AND s_trn.source_barangay = trn.source_barangay ) AS total_aics_food_is_voter,
        COALESCE(COUNT(CASE WHEN service_type = "AICS_EDUC" THEN 1 END), 0) AS total_aics_educ,
        (SELECT COUNT(DISTINCT s_trn.pro_voter_id) FROM tbl_tupad_transaction  s_trn WHERE s_trn.service_type = "AICS_EDUC" AND s_trn.source_municipality = trn.source_municipality AND s_trn.source_barangay = trn.source_barangay ) AS total_aics_educ_uniq,
        (SELECT COUNT(DISTINCT ppv.pro_voter_id) FROM tbl_tupad_transaction  s_trn INNER JOIN tbl_project_voter ppv ON ppv.pro_voter_id = s_trn.pro_voter_id  WHERE s_trn.service_type = "AICS_EDUC" AND ppv.has_photo = 1 AND s_trn.source_municipality = trn.source_municipality AND s_trn.source_barangay = trn.source_barangay ) AS total_aics_educ_w_id,
        (SELECT COUNT(DISTINCT s_trn.pro_voter_id) FROM tbl_tupad_transaction  s_trn WHERE s_trn.service_type = "AICS_EDUC" AND s_trn.is_voter = 1 AND s_trn.source_municipality = trn.source_municipality AND s_trn.source_barangay = trn.source_barangay ) AS total_aics_educ_is_voter,
        COALESCE(COUNT(CASE WHEN service_type = "DISPLACED" THEN 1 END), 0) AS total_displaced,
        (SELECT COUNT(DISTINCT s_trn.pro_voter_id) FROM tbl_tupad_transaction  s_trn WHERE s_trn.service_type = "DISPLACED" AND s_trn.source_municipality = trn.source_municipality AND s_trn.source_barangay = trn.source_barangay ) AS total_displaced_uniq,
        (SELECT COUNT(DISTINCT ppv.pro_voter_id) FROM tbl_tupad_transaction  s_trn INNER JOIN tbl_project_voter ppv ON ppv.pro_voter_id = s_trn.pro_voter_id WHERE s_trn.service_type = "DISPLACED" AND ppv.has_photo = 1 AND s_trn.source_municipality = trn.source_municipality AND s_trn.source_barangay = trn.source_barangay ) AS total_displaced_w_id,
        (SELECT COUNT(DISTINCT s_trn.pro_voter_id) FROM tbl_tupad_transaction  s_trn WHERE s_trn.service_type = "DISPLACED" AND s_trn.is_voter = 1 AND s_trn.source_municipality = trn.source_municipality AND s_trn.source_barangay = trn.source_barangay ) AS total_displaced_is_voter
        
        FROM tbl_tupad_transaction trn INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = trn.pro_voter_id 
        
        WHERE (pv.is_kalaban <> 1 OR pv.is_kalaban IS NULL OR pv.is_kalaban = 0) AND trn.source_municipality = ?
        GROUP BY source_barangay ORDER BY source_barangay ASC ';

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipalityName);
        $stmt->execute();
        $data = [];


        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_get_datatable_tupad_summary_transactions", name="ajax_get_datatable_tupad_summary_transactions", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxGetDatatableTupadSummaryTransactionsAction(Request $request)
    {
        $columns = array(
            0 => "h.id",
            1 => "h.b_name",
            2 => "h.service_type",
            3 => "h.source_municipality",
            4 => "h.source_barangay",
            5 => "h.is_voter",
        );

        $sWhere = "";

        $select['h.b_name'] = $request->get('bName');
        $select['h.service_type'] = $request->get('serviceType');
        $select['h.source_municipality'] = $request->get('sourceMunicipality');
        $select['h.source_barangay'] = $request->get('sourceBarangay');

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

        $em = $this->getDoctrine()->getManager("tupad");
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(h.id),0) FROM tbl_tupad_transaction h WHERE 1 ";

        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.id),0) FROM tbl_tupad_transaction h WHERE 1 ";

        $sql .= $sWhere . " " . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT h.* FROM tbl_tupad_transaction h 
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
     * @Route("/ajax_get_tupad_transction/{trnId}",
     *       name="ajax_get_tupad_transction",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetTupadTransaction($trnId)
    {
        $em = $this->getDoctrine()->getManager("tupad");
        $trn = $em->getRepository("AppBundle:TupadTransaction")
            ->findOneBy([
                'id' => $trnId
            ]);

        if (!$trn) {
            return new JsonResponse(['message' => 'not found']);
        }

        $serializer = $this->get("serializer");
        $trn = $serializer->normalize($trn);

        return new JsonResponse($trn);
    }


    /**
     * @Route("/ajax_get_datatable_tupad_transaction_detail", name="ajax_get_datatable_tupad_transaction_detail", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxGetDatatableTupadTransactionDetailAction(Request $request)
    {
        $columns = array(
            0 => "h.id",
            1 => "h.b_name",
            2 => "h.service_type",
            3 => "h.source_municipality",
            4 => "h.source_barangay",
            5 => "h.is_voter",
        );

        $sWhere = "";

        $select['h.b_name'] = $request->get('bName');
        $select['h.hdr_id'] = $request->get('hdrId');

        foreach ($select as $key => $value) {
            $searchValue = $select[$key];
            if ($searchValue != null || !empty($searchValue)) {
                if ($key == "h.hdr_id") {
                    $sWhere .= " AND " . $key . " = " . $searchValue;
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

        $em = $this->getDoctrine()->getManager("tupad");
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(h.id),0) FROM tbl_tupad_transaction_dtl h WHERE 1 ";

        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.id),0) FROM tbl_tupad_transaction_dtl h WHERE 1 ";

        $sql .= $sWhere . " " . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT h.* FROM tbl_tupad_transaction_dtl h 
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

}