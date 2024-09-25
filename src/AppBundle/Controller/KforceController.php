<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

use AppBundle\Entity\KforceHeader;
use AppBundle\Entity\KforceDetail;

/**
 * @Route("/kforce")
 */

class KforceController extends Controller
{
    const STATUS_ACTIVE = 'A';

    /**
     * @Route("", name="kforce_index", options={"main" = true })
     */

     public function indexAction(Request $request)
     {
         $user = $this->get('security.token_storage')->getToken()->getUser();
         $hostIp = $this->getParameter('host_ip');
         $imgUrl = $this->getParameter('img_url');
 
         return $this->render('template/kforce/index.html.twig', ['user' => $user, "hostIp" => $hostIp, 'imgUrl' => $imgUrl]);
     }

    /**
    * @Route("/ajax_post_kforce_header", 
    * 	name="ajax_post_kforce_header",
    *	options={"expose" = true}
    * )
    * @Method("POST")
    */

    public function ajaxPostKforceHeaderAction(Request $request){

        $eventDate = empty($request->get("eventDate")) ? null : new \DateTime($request->get("eventDate"));

        $entity = new KforceHeader();
    	$entity->setEventName(strtoupper($request->get('eventName')));
        $entity->setEventDesc($request->get('eventDesc'));
        $entity->setEventDate($eventDate);
        $entity->setRemarks($request->get('remarks'));
    	$entity->setStatus(self::STATUS_ACTIVE);

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
     * @Route("/ajax_get_datatable_kforce", name="ajax_get_datatable_kforce", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
	public function ajaxGetDatatableKforceAction(Request $request)
	{	
        $columns = array(
            0 => "e.event_id",
            1 => "e.event_name",
            2 => "e.event_desc"
        );

        $sWhere = "";
    
        $select['e.event_name'] = $request->get('eventName');

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

        $sql = "SELECT COALESCE(count(e.event_id),0) FROM tbl_kforce_header e ";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(e.event_id),0) FROM tbl_kforce_header e
                WHERE 1 ";

        $sql .= $sWhere . ' ' . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT e.* FROM tbl_kforce_header e 
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
    * @Route("/ajax_delete_kforce_header/{eventId}", 
    * 	name="ajax_delete_kforce_header",
    *	options={"expose" = true}
    * )
    * @Method("DELETE")
    */

    public function ajaxDeleteKforceHeaderAction($eventId){

        $em = $this->getDoctrine()->getManager("electPrep2024");
        $entity = $em->getRepository("AppBundle:KforceHeader")->find($eventId);

        if(!$entity)
            return new JsonResponse(null,404);

        $entities = $em->getRepository('AppBundle:KforceDetail')->findBy([
            'eventId' => $entity->getEventId()
        ]);

        foreach($entities as $detail){
            $em->remove($detail);
        }

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null,200);
    }

    
    /**
    * @Route("/ajax_post_kforce_add_attendee", 
    * 	name="ajax_post_kforce_add_attendee",
    *	options={"expose" = true}
    * )
    * @Method("POST")
    */

    public function ajaxPostKforceAddAttendeeAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $projectVoter = $em->getRepository("AppBundle:ProjectVoter")->find($request->get('proVoterId'));
        
        if(!$projectVoter)
            return new JsonResponse(['message' => 'voter not found...'],404);

        $entity = new KforceDetail();
        $entity->setProVoterId($request->get('proVoterId'));
        $entity->setEventId($request->get("eventId"));
        $entity->setVoterName($projectVoter->getVoterName());
        $entity->setVoterGroup($projectVoter->getVoterGroup());
        $entity->setProIdCode($projectVoter->getProIdCode());
        $entity->setGeneratedIdNo($projectVoter->getGeneratedIdNo());
        $entity->setProId($projectVoter->getProId());
        $entity->setCreatedAt(new \DateTime());
        $entity->setCreatedBy($user->getUsername());
    	$entity->setStatus(self::STATUS_ACTIVE);

        if($projectVoter) {
                $proIdCode = !empty($projectVoter->getProIdCode()) ? $projectVoter->getProIdCode() : $this->generateProIdCode($projectVoter->getProId(), $projectVoter->getVoterName(), $projectVoter->getMunicipalityNo()) ;
                $entity->setProIdCode($proIdCode);
                $projectVoter->setProIdCode($proIdCode);
        }

    	$validator = $this->get('validator');
        $violations = $validator->validate($entity);

        $errors = [];

        if(count($violations) > 0){
            foreach( $violations as $violation ){
                $errors[$violation->getPropertyPath()] =  $violation->getMessage();
            }
            return new JsonResponse($errors,400);
        }
        
        $projectVoter->setCellphone($request->get('cellphone'));
        $projectVoter->setVoterGroup(trim(strtoupper($request->get('voterGroup'))));
        $projectVoter->setUpdatedAt(new \DateTime());
        $projectVoter->setUpdatedBy($user->getUsername());
        $projectVoter->setIsKalaban(1);

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
     * @Route("/ajax_datatable_kforce_detail",
     *     name="ajax_datatable_kforce_detail",
     *     options={"expose" = true}
     *     )
     * @Method("GET")
     */

     public function datatableKforceDetailAction(Request $request)
     {
         $user = $this->get('security.token_storage')->getToken()->getUser();
         $eventId = $request->get("eventId");
         
         $filters = array();
         $filters['d.event_id'] = $request->get('eventId');
         $filters['pv.province_code']  = $request->get('provinceCode');
        
         $filters['b.name'] = strtoupper(trim($request->get('barangayName')));
         $filters['pv.voter_name'] = strtoupper(trim($request->get('voterName')));
         $filters['pv.voter_group'] = strtoupper(trim($request->get('voterGroup')));
 
         $columns = array(
             0 => 'pv.voter_id',
             1 => 'pv.voter_name',
             2 => 'pv.voter_group'
         );
 
 
         $whereStmt = " AND (";
         
         foreach($filters as $field => $searchText){
             if($searchText != ""){
                 if($field == 'd.event_id' ){
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
 
         $orderStmt = " ORDER BY d.voter_name ASC";
         
         $start = 0;
         $length = 1;
 
         if(null !== $request->query->get('start') && null !== $request->query->get('length')){
             $start = intval($request->query->get('start'));
             $length = intval($request->query->get('length'));
         }
         
         $em = $this->getDoctrine()->getManager();
         $em->getConnection()->getConfiguration()->setSQLLogger(null);
 
         $sql = "SELECT COALESCE(count(d.event_detail_id),0) FROM tbl_kforce_detail d
                 INNER JOIN tbl_project_voter pv ON d.pro_voter_id = pv.pro_voter_id WHERE d.event_id = {$eventId} ";
         $stmt = $em->getConnection()->query($sql);
         
         $recordsTotal = $stmt->fetchColumn();
 
         $sql = "SELECT COALESCE(COUNT(d.event_detail_id),0) FROM tbl_kforce_detail d
                 INNER JOIN tbl_project_voter pv ON d.pro_voter_id = pv.pro_voter_id 
                 WHERE 1 ";
        
 
         $sql .= $whereStmt . ' ' . $orderStmt;
         $stmt = $em->getConnection()->query($sql);
         $recordsFiltered = $stmt->fetchColumn();
         
         $sql = "SELECT pv.* , d.event_detail_id FROM tbl_kforce_detail d
                 INNER JOIN tbl_project_voter pv ON d.pro_voter_id = pv.pro_voter_id
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
    * @Route("/ajax_delete_kforce_detail/{eventDetailId}", 
    * 	name="ajax_delete_kforce_detail",
    *	options={"expose" = true}
    * )
    * @Method("DELETE")
    */

    public function ajaxDeleteKforceDetailAction($eventDetailId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:KforceDetail")->find($eventDetailId);

        if(!$entity)
            return new JsonResponse(null,404);

        $projectVoter = $em->getRepository("AppBundle:ProjectVoter")->find($entity->getProVoterId());
        $projectVoter->isKalaban(0);

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null,200);
    }

     /**
    * @Route("/ajax_get_kforce_header/{eventId}", 
    * 	name="ajax_get_kforce_header",
    *	options={"expose" = true}
    * )
    * @Method("GET")
    */

    public function ajaxGetKforceAction($eventId){
        $em = $this->getDoctrine()->getManager("electPrep2024");
        $entity = $em->getRepository("AppBundle:KforceHeader")->find($eventId);

        if(!$entity)
            return new JsonResponse(null,404);

        $serializer = $this->get("serializer");
        
        return new JsonResponse($serializer->normalize($entity));
    }

    /**
    * @Route("/ajax_patch_kforce_header/{eventId}", 
    * 	name="ajax_patch_kforce_header",
    *	options={"expose" = true}
    * )
    * @Method("PATCH")
    */

    public function ajaxPatchKforceHeaderAction(Request $request,$eventId){
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository("AppBundle:KforceHeader")->find($eventId);

        if(!$entity)
            return new JsonResponse(null,404);

        $eventDate = empty($request->get("eventDate")) ? null : new \DateTime($request->get("eventDate"));
        
        $entity->setEventName(strtoupper($request->get('eventName')));
        $entity->setEventDesc($request->get('eventDesc'));
        $entity->setEventDate($eventDate);
        
    	$validator = $this->get('validator');
        $violations = $validator->validate($entity);

        $errors = [];

        if(count($violations) > 0){
            foreach( $violations as $violation ){
                $errors[$violation->getPropertyPath()] =  $violation->getMessage();
            }
            return new JsonResponse($errors,400);
        }
        
        $em->flush();
    	$em->clear();

    	$serializer = $this->get('serializer');

    	return new JsonResponse($serializer->normalize($entity));
    }


}
