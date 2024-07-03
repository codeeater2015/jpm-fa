<?php
namespace AppBundle\Controller;

use AppBundle\Entity\ProjectVoter;
use AppBundle\Entity\SendSms;
use AppBundle\Entity\Voter;
use AppBundle\Entity\VoterAssistance;
use AppBundle\Entity\VoterAssistanceSummary;
use AppBundle\Entity\VoterSummary;
use AppBundle\Entity\TempBcbpProfile;
use Box\Spout\Common\Type;
use Box\Spout\Reader\ReaderFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @Route("/bcbp")
 */

class BcbpController extends Controller
{
    /**
     * @Route("", name="bcbp_index", options={"main" = true })
     */

    public function indexAction(Request $request)
    {
        return $this->render('template/bcbp/index.html.twig');
    }

    /**
     * @Route("/ajax_get_datatable_bcbp_temp_profile", name="ajax_get_datatable_bcbp_temp_profile", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxGetDatatableBcbpTempProfileAction(Request $request)
    {
        $columns = array(
            0 => "h.id",
            1 => "h.name",
            2 => "h.group_name",
            3 => "h.batch_name",
            4 => "h.chapter_name",
            5 => "h.contact_number",
            6 => "h.gender",
            7 => "h.birthdate",
            8 => "h.source_number",
            9 => "h.created_by",
        );

        $sWhere = "";

        $select['h.name'] = $request->get('name');
        $select['h.group_name'] = $request->get('groupName');
        $select['h.unit_name'] = $request->get('unitName');
        $select['h.position'] = $request->get('position');
        $select['h.batch_name'] = $request->get('batchName');
        $select['h.chapter_name'] = $request->get('chapterName');
        $select['h.source_number'] = $request->get('sourceNumber');
        $select['h.contact_number'] = $request->get('contactNumber');
        $select['h.gender'] = $request->get('gender');

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

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(h.id),0) FROM tbl_temp_bcbp_profile h WHERE 1 ";

        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.id),0) FROM tbl_temp_bcbp_profile h WHERE 1 ";

        $sql .= $sWhere . " " . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT h.* FROM tbl_temp_bcbp_profile h 
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
     * @Route("/ajax_select2_bcbp_chapter",
     *       name="ajax_select2_bcbp_chapter",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2BcbpChapter(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT p.chapter_name FROM tbl_temp_bcbp_profile p WHERE p.chapter_name LIKE ? ORDER BY p.chapter_name ASC LIMIT 30";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->execute();

        $chapter = $stmt->fetchAll();

        if (count($chapter) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($chapter);
    }

    /**
     * @Route("/ajax_select2_bcbp_group",
     *       name="ajax_select2_bcbp_group",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2BcbpGroup(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT p.group_name FROM tbl_temp_bcbp_profile p WHERE p.group_name LIKE ? ORDER BY p.group_name ASC LIMIT 30";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->execute();

        $chapter = $stmt->fetchAll();

        if (count($chapter) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($chapter);
    }

    /**
     * @Route("/ajax_select2_bcbp_unit",
     *       name="ajax_select2_bcbp_unit",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2BcbpUnit(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT p.unit_name FROM tbl_temp_bcbp_profile p WHERE p.unit_name LIKE ? ORDER BY p.unit_name ASC LIMIT 30";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->execute();

        $chapter = $stmt->fetchAll();

        if (count($chapter) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($chapter);
    }


     /**
     * @Route("/ajax_select2_bcbp_position",
     *       name="ajax_select2_bcbp_position",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

     public function ajaxSelect2BcbpPosition(Request $request)
     {
         $em = $this->getDoctrine()->getManager();
         $searchText = trim(strtoupper($request->get('searchText')));
         $searchText = '%' . strtoupper($searchText) . '%';
 
         $sql = "SELECT DISTINCT p.position FROM tbl_temp_bcbp_profile p WHERE p.position LIKE ? ORDER BY p.position ASC LIMIT 30";
         $stmt = $em->getConnection()->prepare($sql);
         $stmt->bindValue(1, $searchText);
         $stmt->execute();
 
         $chapter = $stmt->fetchAll();
 
         if (count($chapter) <= 0) {
             return new JsonResponse(array());
         }
 
         $em->clear();
 
         return new JsonResponse($chapter);
     }

    /**
     * @Route("/ajax_select2_bcbp_gender",
     *       name="ajax_select2_bcbp_gender",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2BcbpGender(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT p.gender FROM tbl_temp_bcbp_profile p WHERE p.gender LIKE ? ORDER BY p.gender ASC LIMIT 30";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->execute();

        $chapter = $stmt->fetchAll();

        if (count($chapter) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($chapter);
    }

    /**
     * @Route("/ajax_select2_bcbp_batch",
     *       name="ajax_select2_bcbp_batch",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2BcbpBatch(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT p.batch_name FROM tbl_temp_bcbp_profile p WHERE p.batch_name LIKE ? ORDER BY p.batch_name ASC LIMIT 30";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->execute();

        $chapter = $stmt->fetchAll();

        if (count($chapter) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($chapter);
    }

    /**
     * @Route("/ajax_sms_multiselect_bcbp_member",
     *   name="ajax_sms_multiselect_bcbp_member",
     *   options={"expose" = true}
     * )
     * @Method("GET")
     */

    public function ajaxSmsMultiselectBcbpMemberAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $chapterName = empty($request->get('chapterName')) ? null : $request->get('chapterName');
        $groupName = empty($request->get('groupName')) ? null : $request->get('groupName');
        $unitName = empty($request->get('unitName')) ? null : $request->get('unitName');
        $position = empty($request->get('position')) ? null : $request->get('position');
        $batchName = empty($request->get('batchName')) ? null : $request->get('batchName');
        $gender = empty($request->get('gender')) ? null : $request->get('gender');
        $withBirthdate = empty($request->get('withBirthdate')) ? null : $request->get('withBirthdate');

        
        $todayDate = date('m-d');


        $sql = "SELECT p.* FROM tbl_temp_bcbp_profile p WHERE
             (p.chapter_name = ? OR ? IS NULL)  AND
             (p.group_name = ? OR ? IS NULL) AND
             (p.unit_name = ? OR ? IS NULL) AND
             (p.batch_name = ? OR ? IS NULL) AND
             (p.gender = ? OR ? IS NULL) AND
             (p.contact_number <> '' AND p.contact_number IS NOT NULL) ";


        if ($withBirthdate) {
            $sql .= " AND (birthdate LIKE '%{$todayDate}%') ";
        }

        $sql .= " ORDER BY p.name ASC ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $chapterName);
        $stmt->bindValue(2, empty($chapterName) ? null : $chapterName);

        $stmt->bindValue(3, $groupName);
        $stmt->bindValue(4, empty($groupName) ? null : $groupName);

        $stmt->bindValue(5, $batchName);
        $stmt->bindValue(6, empty($batchName) ? null : $batchName);

        $stmt->bindValue(7, $unitName);
        $stmt->bindValue(8, empty($unitName) ? null : $unitName);

        $stmt->bindValue(9, $gender);
        $stmt->bindValue(10, empty($gender) ? null : $gender);


        $stmt->execute();

        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (!is_array($data) || count($data) <= 0) {
            $data = [];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_post_bcbp_sms",
     *       name="ajax_post_bcbp_sms",
     *       options={ "expose" = true }
     * )
     * @Method("POST")
     */

    public function ajaxPostBcbpSms(Request $request)
    {
        $self = $this;
        $user = $this->get("security.token_storage")->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $currentRow = 0;
        $totalRows = 0;
        $percentage = 0;
        $currentVoter = "";

        $messageBody = $request->get("messageBody");
        $voters = $request->get("voters");

        if (!$user->getIsAdmin()) {
            return new JsonResponse(null, 401);
        }

        $errors = [];

        if (empty($messageBody)) {
            $errors['messageBody'] = 'Your message cannot be empty...';
        }

        if (count($voters) <= 0) {
            $errors['voters'] = 'Please select 1 or more message recipient...';
        }

        if (count($errors) > 0) {
            return new JsonResponse($errors, 400);
        }

        $response = new StreamedResponse();
        $response->headers->set("Cache-Control", "no-cache, must-revalidate");
        $response->headers->set("X-Accel-Buffering", "no");
        $response->setStatusCode(200);

        $response->setCallback(function () use ($self, $em, $user, $request) {
            $voters = $request->get("voters");
            $totalRows = count($voters);
            $counter = 0;

            foreach ($voters as $id) {
                $counter++;

                $sql = "SELECT p.* FROM tbl_temp_bcbp_profile p WHERE p.id = ? ";

                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $id);
                $stmt->execute();
                $member = $stmt->fetch(\PDO::FETCH_ASSOC);

                if (!$member) {
                    echo json_encode([
                        'totalRows' => $totalRows,
                        'currentRowIndex' => $counter,
                        'currentRow' => [
                            'voter_name' => $id,
                        ],
                        'status' => false,
                        'percentage' => (int) (($counter / $totalRows) * 100),
                        'message' => 'Message failed Id : ' . $id,
                    ]);
                }

                $messageText = $request->get('messageBody');

                $transArr = array(
                    '{fn}' => ucwords(strtolower($member['firstname'])),
                    '{mn}' => ucwords(strtolower($member['middlename'])),
                    '{ln}' => ucwords(strtolower($member['lastname'])),
                    '{px}' => ucwords(strtolower($member['gender'] == 'MALE' ? "Bro" : "Sis")),
                    '{nn}' => ucwords(strtolower($member['nickname'])),
                    '{n}' => ucwords(strtolower($member['name'])),
                    '{firstname}' => ucwords(strtolower($member['firstname'])),
                    '{middlename}' => ucwords(strtolower($member['middlename'])),
                    '{lastname}' => ucwords(strtolower($member['lastname'])),
                    '{prefix}' => ucwords(strtolower($member['gender'] == 'MALE' ? "Bro" : "Sis")),
                    '{nickname}' => ucwords(strtolower($member['nickname'])),
                    '{name}' => ucwords(strtolower($member['name']))
                );

                $messageText = strtr($messageText, $transArr);

                if ($member) {
                    $contactNo = $member['contact_number'];

                    //if (preg_match("/^(09)\\d{9}$/", $contactNo)) {
                        $msgEntity = new SendSms();
                        $msgEntity->setMessageText($messageText);
                        $msgEntity->setMessageTo("" .  $contactNo);
                        $em->persist($msgEntity);
                        $em->flush();
                    //}
                }

                $em->clear();

                //sleep(1);

                echo json_encode([
                    'totalRows' => $totalRows,
                    'currentRowIndex' => $counter,
                    'currentRow' => $member,
                    'message' => $messageText,
                    'percentage' => (int) (($counter / $totalRows) * 100),
                    'status' => true,
                ]);

                ob_flush();
                flush();
            }
        });

        return $response;
    }

    /**
     * @Route("/ajax_bcbp_post_profile", 
     * 	name="ajax_bcbp_post_profile",
     *	options={"expose" = true}
     * )
     * @Method("POST")
     */

    public function ajaxPostBcbpProfileAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        $entity = new TempBcbpProfile();
        $entity->setChapterName(strtoupper($request->get("chapterName")));
        $entity->setBatchName(strtoupper($request->get('batchName')));
        $entity->setGroupName(strtoupper($request->get('groupName')));
        $entity->setUnitName(strtoupper($request->get('unitName')));
        $entity->setPosition(strtoupper($request->get('position')));
        $entity->setFirstname(strtoupper($request->get('firstname')));
        $entity->setLastname(strtoupper($request->get('lastname')));
        $entity->setNickname(strtoupper($request->get('nickname')));
        $entity->setName(strtoupper($request->get('firstname')) . ' ' . strtoupper($request->get('lastname')) );
        $entity->setBirthdate($request->get('birthdate'));
        $entity->setGender(strtoupper($request->get('gender')));
        $entity->setContactNumber($request->get('contactNumber'));
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

        $em->persist($entity);
        $em->flush();

        $em->clear();
        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($entity));
    }

    /**
     * @Route("/ajax_get_bcbp_profile/{id}",
     *       name="ajax_get_bcbp_profile",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetBcbpProfile($id)
    {
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository("AppBundle:TempBcbpProfile")
            ->findOneBy([
                'id' => $id
            ]);

        if (!$profile) {
            return new JsonResponse(['message' => 'not found']);
        }

        $serializer = $this->get("serializer");

        return new JsonResponse($serializer->normalize($profile));
    }

    /**
     * @Route("/ajax_bcbp_patch_profile", 
     * 	name="ajax_bcbp_patch_profile",
     *	options={"expose" = true}
     * )
     * @Method("PATCH")
     */
    public function ajaxPatchBcbpProfileAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository("AppBundle:TempBcbpProfile")
            ->findOneBy([
                'id' => $request->get("id")
            ]);

        if ($entity) {
            $entity->setChapterName(strtoupper($request->get("chapterName")));
            $entity->setBatchName(strtoupper($request->get('batchName')));
            $entity->setGroupName(strtoupper($request->get('groupName')));
            $entity->setUnitName(strtoupper($request->get('unitName')));
            $entity->setPosition(strtoupper($request->get('position')));
            $entity->setFirstname(strtoupper($request->get('firstname')));
            $entity->setLastname(strtoupper($request->get('lastname')));
            $entity->setName(strtoupper($request->get('firstname')) . ' ' . strtoupper($request->get('lastname')) );
            $entity->setBirthdate($request->get('birthdate'));
            $entity->setGender(strtoupper($request->get('gender')));
            $entity->setContactNumber($request->get('contactNumber'));
            $entity->setNickname(strtoupper($request->get('nickname')));

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

            $em->clear();
            $serializer = $this->get('serializer');

            return new JsonResponse($serializer->normalize($entity));
        }
    }

    
   
 
}