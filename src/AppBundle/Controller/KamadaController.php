<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
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

            $entity->setProIdCode($proVoter->getProIdCode());
            $entity->setGeneratedIdNo($proVoter->getGeneratedIdNo());
            $entity->setVoterName($proVoter->getVoterName());
            $entity->setVoterGroup($request->get('voterGroup'));
            $entity->setCellphone($request->get('cellphone'));
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
 
         $sql = "SELECT h.*,pv.is_non_voter, pv.voter_group FROM tbl_kamada_hdr h 
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

        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy(['proVoterId' => intval($request->get('proVoterId'))]);

        if ($proVoter) {
            $entity->setVoterName($proVoter->getVoterName());
            $entity->setProIdCode($proVoter->getProIdCode());
            $entity->setGeneratedIdNo($proVoter->getGeneratedIdNo());
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
 
}
