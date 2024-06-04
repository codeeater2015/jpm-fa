<?php
namespace AppBundle\Controller;

use AppBundle\Entity\BcbpMember;
use AppBundle\Entity\PendingVoter;
use AppBundle\Entity\ProjectEventDetail;
use AppBundle\Entity\ProjectVoter;
use AppBundle\Entity\Voter;
use AppBundle\Entity\BcbpEventHeader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use AppBundle\Entity\ProjectVoterSummary;
use AppBundle\Entity\ProjectVoterSummaryAssigned;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

use Box\Spout\Writer\Style\Border;
use Box\Spout\Writer\Style\BorderBuilder;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;


/**
 * @Route("/mobi")
 */

class MobileController extends Controller
{
    const ACTIVE_ELECTION = 423;
    const ACTIVE_PROJECT = 3;
    const ACTIVE_STATUS = 'A';

    /**
     * @Route("/ajax_m_get_municipalities",
     *       name="ajax_m_get_municipalities",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetMunicipality(Request $request)
    {
        $provinceCode = 53;

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT * FROM psw_municipality m WHERE m.province_code = ? AND m.municipality_no IN ('01','16') ORDER BY m.name ASC";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);

        $stmt->execute();
        $municipalities = $stmt->fetchAll();

        if (count($municipalities) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($municipalities);
    }

    /**
     * @Route("/ajax_m_get_barangays/{municipalityCode}",
     *       name="ajax_m_get_barangays",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetBarangays(Request $request, $municipalityCode)
    {

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT * FROM psw_barangay b
                WHERE b.municipality_code = ? ORDER BY b.name ASC";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipalityCode);

        $stmt->execute();
        $barangays = $stmt->fetchAll();

        if (count($barangays) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($barangays);
    }

    /**
     * @Route("/ajax_m_get_project_voters",
     *       name="ajax_m_get_project_voters",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetProjectVoters(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $provinceCode = substr($request->get("municipalityCode"), 0, 2);
        $municipalityNo = substr($request->get("municipalityCode"), -2);
        $brgyNo = $request->get("brgyNo");
        $voterName = $request->get("voterName");
        $imgUrl = $this->getParameter('img_url');
        $batchSize = 3;
        $batchNo = $request->get("batchNo");

        $batchOffset = $batchNo * $batchSize;

        $sql = "SELECT pv.* FROM tbl_project_event_header eh 
                LEFT JOIN tbl_project_event_detail ed
                ON  ed.event_id = eh.event_id
                INNER JOIN tbl_project_voter pv 
                ON pv.pro_voter_id = ed.pro_voter_id 
                WHERE eh.status = 'A' AND pv.has_id <> 1 AND pv.has_photo <> 1 AND ";

        if (!is_numeric($voterName)) {
            $sql .= " (pv.voter_name LIKE ? OR ? IS NULL ) ";
        } else {
            $sql .= " (pv.generated_id_no LIKE ? OR ? IS NULL ) ";
        }

        $sql .= "AND pv.elect_id = ? ORDER BY pv.voter_name ASC LIMIT {$batchSize} OFFSET {$batchOffset}";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, '%' . $voterName . '%');
        $stmt->bindValue(2, empty($voterName) ? null : '%' . $voterName . '%');
        $stmt->bindValue(3, self::ACTIVE_ELECTION);
        $stmt->execute();

        $municipalities = $this->getMunicipalities(53);


        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['imgUrl'] = $imgUrl . '3_' . $row['generated_id_no'] . '?' . strtotime((new \DateTime())->format('Y-m-d H:i:s'));
            $row['cellphone_no'] = $row['cellphone'];
            $data[] = $row;
        }

        foreach ($data as &$row) {
            //$lgc = $this->getLGC($row['municipality_no'], $row['brgy_no']);
            $row['lgc'] = [
                'voter_name' => '- disabled -',
                //$lgc['voter_name'],
                'cellphone' => '- disabled -' //$lgc['cellphone']
            ];
        }

        return new JsonResponse($data);
    }

    private function getLGC($municipalityNo, $barangayNo)
    {
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT pv.* FROM tbl_location_assignment l INNER JOIN tbl_project_voter pv ON pv.pro_id_code = l.pro_id_code 
        WHERE l.municipality_no = ? AND l.barangay_no = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipalityNo);
        $stmt->bindValue(2, $barangayNo);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row == null ? ['voter_name' => "No LGC", 'cellphone' => 'No LGC'] : $row;
    }


    private function getProjectVoter($proId, $voterId)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:ProjectVoter")->findOneBy(
            [
                "voterId" => $voterId,
                "proId" => $proId,
            ]
        );

        return $entity;
    }


    /**
     * @Route("/ajax_m_get_project_voters_all",
     *       name="ajax_m_get_project_voters_all",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetProjectVotersAll(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $provinceCode = substr($request->get("municipalityCode"), 0, 2);
        $municipalityNo = substr($request->get("municipalityCode"), -2);
        $municipalityName = $request->get('municipalityName');
        $barangayName = $request->get('barangayName');
        $voterGroup = $request->get('voterGroup');

        $brgyNo = $request->get("brgyNo");
        $voterName = $request->get("voterName");
        $imgUrl = $this->getParameter('img_url');
        $batchSize = 3;
        $batchNo = $request->get("batchNo");

        $batchOffset = $batchNo * $batchSize;

        $sql = "SELECT pv.* FROM tbl_project_voter pv WHERE 1 AND ";

        if (!is_numeric($voterName)) {
            $sql .= " (pv.voter_name LIKE ? OR ? IS NULL ) ";
        } else {
            $sql .= " (pv.generated_id_no LIKE ? OR ? IS NULL ) ";
        }

        $sql .= "AND pv.elect_id = ? 
        AND (pv.municipality_name = ? OR ? IS NULL) 
        AND (pv.barangay_name = ? OR ? IS NULL) 
        AND (pv.voter_group = ? OR ? IS NULL) 
        ORDER BY pv.voter_name ASC LIMIT {$batchSize} OFFSET {$batchOffset}";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, '%' . $voterName . '%');
        $stmt->bindValue(2, empty($voterName) ? null : '%' . $voterName . '%');
        $stmt->bindValue(3, self::ACTIVE_ELECTION);
        $stmt->bindValue(4, $municipalityName);
        $stmt->bindValue(5, empty($municipalityName) ? null : $municipalityName);
        $stmt->bindValue(6, $barangayName);
        $stmt->bindValue(7, empty($barangayName) ? null : $barangayName);
        $stmt->bindValue(8, $voterGroup);
        $stmt->bindValue(9, empty($voterGroup) ? null : $voterGroup);
        $stmt->execute();

        $municipalities = $this->getMunicipalities(53);


        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['imgUrl'] = $imgUrl . '3_' . $row['generated_id_no'] . '?' . strtotime((new \DateTime())->format('Y-m-d H:i:s'));
            $row['cellphone_no'] = $row['cellphone'];
            $data[] = $row;
        }

        foreach ($data as &$row) {
            $lgc = $this->getLGC($row['municipality_no'], $row['brgy_no']);
            $row['lgc'] = [
                'voter_name' => '- disabled -',
                //$lgc['voter_name'],
                'cellphone' => '- disabled -' //$lgc['cellphone']
            ];
        }

        return new JsonResponse($data);
    }


    /**
     * @Route("/ajax_m_summary_dates",
     *       name="ajax_m_summary_dates",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSummaryDates(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $electId = $request->get('electId');
        $proId = $request->get('proId');

        $sql = "SELECT DISTINCT created_at
                FROM tbl_project_voter_summary
                WHERE elect_id = ? AND pro_id = ?
                ORDER BY created_at DESC LIMIT 10";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $electId);
        $stmt->bindValue(2, $proId);
        $stmt->execute();

        $dates = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $dates[] = $row;
        }

        return new JsonResponse($dates);
    }

    /**
     * @Route("/ajax_get_m_get_prev_summary_date",
     *       name="ajax_get_m_get_prev_summary_date",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function getPrevSummaryDate(Request $request)
    {
        $em = $this->getDoctrine();
        $electId = $request->get('electId');
        $proId = $request->get('proId');
        $createdAt = $request->get("createdAt");

        $sql = "SELECT DISTINCT created_at
        FROM tbl_project_voter_summary
        WHERE elect_id = ? AND pro_id = ? AND created_at < ?
        ORDER BY created_at DESC LIMIT 1";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $electId);
        $stmt->bindValue(2, $proId);
        $stmt->bindValue(3, $createdAt);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row == null ? new JsonResponse(['created_at' => null]) : new JsonResponse($row);
    }

    /**
     * @Route("/ajax_m_get_project_voter/{proId}/{generatedIdNo}",
     *       name="ajax_m_get_project_voter",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetProjectVoter($proId, $generatedIdNo)
    {
        $em = $this->getDoctrine()->getManager();
        $imgUrl = $this->getParameter('img_url');


        $projectVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy([
            'generatedIdNo' => $generatedIdNo,
            'proId' => $proId,
            'electId' => 4,
        ]);

        if (!$projectVoter) {
            return new JsonResponse(null, 404);
        }

        $serializer = $this->get('serializer');

        $projectVoter = $serializer->normalize($projectVoter);
        $projectVoter['imgUrl'] = $imgUrl . $proId . '_' . $projectVoter['generatedIdNo'] . '?' . strtotime((new \DateTime())->format('Y-m-d H:i:s'));
        $projectVoter['cellphoneNo'] = $projectVoter['cellphone'];

        $lgc = $this->getLGC($projectVoter['municipalityNo'], $projectVoter['brgyNo']);
        $projectVoter['lgc'] = [
            'voter_name' => $lgc['voter_name'],
            'cellphone' => $lgc['cellphone']
        ];

        return new JsonResponse($projectVoter);
    }

    /**
     * @Route("/ajax_m_get_project_voter_alt/{proId}/{proVoterId}",
     *       name="ajax_m_get_project_voter_alt",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetProjectVoterAlt($proId, $proVoterId)
    {

        $em = $this->getDoctrine()->getManager();
        $imgUrl = $this->getParameter('img_url');

        $projectVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy([
            'proVoterId' => $proVoterId,
            'proId' => $proId,
            'electId' => self::ACTIVE_ELECTION,
        ]);

        if (!$projectVoter) {
            return new JsonResponse(null, 404);
        }

        $serializer = $this->get('serializer');

        $projectVoter = $serializer->normalize($projectVoter);
        $projectVoter['imgUrl'] = $imgUrl . $proId . '_' . $projectVoter['generatedIdNo'] . '?' . strtotime((new \DateTime())->format('Y-m-d H:i:s'));
        $projectVoter['cellphoneNo'] = $projectVoter['cellphone'];

        $lgc = $this->getLGC($projectVoter['municipalityNo'], $projectVoter['brgyNo']);
        $projectVoter['lgc'] = [
            'voter_name' => $lgc['voter_name'],
            'cellphone' => $lgc['cellphone']
        ];

        return new JsonResponse($projectVoter);
    }

    /**
     * @Route("/ajax_m_get_active_event/{proId}",
     *       name="ajax_m_active_event",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetActiveEvent($proId)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository("AppBundle:ProjectEventHeader")->findOneBy([
            'proId' => $proId,
            'status' => self::ACTIVE_STATUS,
        ]);

        if (!$event) {
            return new JsonResponse(null, 404);
        }

        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($event));
    }

    /**
     * @Route("/ajax_m_get_active_event_barangays/{proId}",
     *       name="ajax_m_active_event_barangays",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetActiveEventBarangays($proId)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository("AppBundle:ProjectEventHeader")->findOneBy([
            'proId' => $proId,
            'status' => self::ACTIVE_STATUS,
        ]);

        if (!$event) {
            return new JsonResponse(null, 404);
        }

        $sql = "SELECT DISTINCT pv.barangay_name FROM tbl_project_event_detail ed 
                INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = ed.pro_voter_id 
                WHERE ed.event_id = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $event->getEventid());
        $stmt->execute();

        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $serializer = $this->get('serializer');

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_m_get_active_event_attendees",
     *       name="ajax_m_active_event_attendees",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetActiveEventAttendees(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $imgUrl = $this->getParameter('img_url');

        $batchSize = 10;
        $batchNo = $request->get("batchNo");
        $voterName = $request->get("voterName");
        $eventId = $request->get('eventId');
        $barangayName = $request->get('barangayName');

        $batchOffset = $batchNo * $batchSize;

        $event = $em->getRepository("AppBundle:ProjectEventHeader")->findOneBy([
            'eventId' => $eventId,
            'status' => self::ACTIVE_STATUS,
        ]);

        if (!$event) {
            return new JsonResponse(null, 404);
        }

        $sql = "SELECT
        COALESCE(SUM( CASE WHEN ed.has_attended = 1 THEN 1 ELSE 0 END),0) AS total_attended,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' THEN 1 ELSE 0 END),0) AS total_lgc,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' THEN 1 ELSE 0 END),0) AS total_lgo,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' THEN 1 ELSE 0 END),0) AS total_lopp,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' THEN 1 ELSE 0 END),0) AS total_lppp,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' THEN 1 ELSE 0 END),0) AS total_lppp1,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' THEN 1 ELSE 0 END),0) AS total_lppp2,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' THEN 1 ELSE 0 END),0) AS total_lppp3,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' THEN 1 ELSE 0 END),0) AS total_jpm,
        COALESCE(COUNT(ed.event_detail_id),0) AS total_expected
        FROM tbl_project_event_detail ed INNER JOIN tbl_project_voter pv 
        ON pv.pro_voter_id = ed.pro_voter_id 
        WHERE ed.event_id = ? AND (pv.barangay_name = ? OR ? IS NULL )";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $eventId);
        $stmt->bindValue(2, strtoupper(trim($barangayName)));
        $stmt->bindValue(3, empty($barangayName) ? null : $barangayName);
        $stmt->execute();

        $summary = $stmt->fetch(\PDO::FETCH_ASSOC);

        $sql = "SELECT pv.*
        FROM tbl_project_event_detail ed
        INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = ed.pro_voter_id
        WHERE ed.event_id  = ? AND (pv.voter_name LIKE ? OR ? IS NULL ) 
        AND (pv.barangay_name = ? OR ? IS NULL) 
        ORDER BY ed.attended_at DESC LIMIT {$batchSize} OFFSET {$batchOffset}";

        //return new JsonResponse($sql);

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $eventId);
        $stmt->bindValue(2, '%' . strtoupper(trim($voterName)) . '%');
        $stmt->bindValue(3, empty($voterName) ? null : $voterName);
        $stmt->bindValue(4, strtoupper(trim($barangayName)));
        $stmt->bindValue(5, empty($barangayName) ? null : $barangayName);
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['imgUrl'] = $imgUrl . self::ACTIVE_PROJECT . '_' . $row['generated_id_no'] . '?' . strtotime((new \DateTime())->format('Y-m-d H:i:s'));
            $row['cellphone_no'] = $row['cellphone'];
            $data[] = $row;
        }

        foreach ($data as &$row) {
            //$lgc = $this->getLGC($row['municipality_no'], $row['brgy_no']);
            $row['lgc'] = [
                'voter_name' => '- disabled -',
                //$lgc['voter_name'],
                'cellphone' => '- disabled -' //$lgc['cellphone']
            ];
        }

        return new JsonResponse([
            "data" => $data,
            "totalExpected" => $summary['total_expected'],
            "totalAttended" => $summary['total_attended'],
            "totalLgc" => $summary['total_lgc'],
            'totalLgo' => $summary['total_lgo'],
            "totalLopp" => $summary['total_lopp'],
            "totalLppp" => $summary['total_lppp'],
            "totalLppp1" => $summary['total_lppp1'],
            'totalLppp2' => $summary['total_lppp2'],
            'totalLppp3' => $summary['total_lppp3'],
            'totalJpm' => $summary['total_jpm']
        ]);
    }

    /**
     * @Route("/ajax_m_get_active_event_attendees_pending_photo",
     *       name="ajax_m_get_active_event_attendees_pending_photo",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetActiveEventAttendeesPendingPhoto(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $imgUrl = $this->getParameter('img_url');

        $batchSize = 10;
        $batchNo = $request->get("batchNo");
        $voterName = $request->get("voterName");
        $eventId = $request->get('eventId');
        $barangayName = $request->get('barangayName');

        $batchOffset = $batchNo * $batchSize;

        $event = $em->getRepository("AppBundle:ProjectEventHeader")->findOneBy([
            'eventId' => $eventId,
            'status' => self::ACTIVE_STATUS,
        ]);

        if (!$event) {
            return new JsonResponse(null, 404);
        }

        $sql = "SELECT
         COALESCE(SUM( CASE WHEN ed.has_attended = 1 THEN 1 ELSE 0 END),0) AS total_attended,
         COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' THEN 1 ELSE 0 END),0) AS total_lgc,
         COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' THEN 1 ELSE 0 END),0) AS total_lgo,
         COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' THEN 1 ELSE 0 END),0) AS total_lopp,
         COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' THEN 1 ELSE 0 END),0) AS total_lppp,
         COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' THEN 1 ELSE 0 END),0) AS total_lppp1,
         COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' THEN 1 ELSE 0 END),0) AS total_lppp2,
         COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' THEN 1 ELSE 0 END),0) AS total_lppp3,
         COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' THEN 1 ELSE 0 END),0) AS total_jpm,
         COALESCE(COUNT(ed.event_detail_id),0) AS total_expected
         FROM tbl_project_event_detail ed INNER JOIN tbl_project_voter pv 
         ON pv.pro_voter_id = ed.pro_voter_id 
         WHERE ed.event_id = ? AND (pv.barangay_name = ? OR ? IS NULL )";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $eventId);
        $stmt->bindValue(2, strtoupper(trim($barangayName)));
        $stmt->bindValue(3, empty($barangayName) ? null : $barangayName);
        $stmt->execute();

        $summary = $stmt->fetch(\PDO::FETCH_ASSOC);

        $sql = "SELECT pv.*
         FROM tbl_project_event_detail ed
         INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = ed.pro_voter_id
         WHERE ed.event_id  = ? AND (pv.voter_name LIKE ? OR ? IS NULL ) 
         AND (pv.barangay_name = ? OR ? IS NULL) 
         AND pv.has_photo_2023 = 0
         ORDER BY ed.attended_at DESC LIMIT {$batchSize} OFFSET {$batchOffset}";

        //return new JsonResponse($sql);

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $eventId);
        $stmt->bindValue(2, '%' . strtoupper(trim($voterName)) . '%');
        $stmt->bindValue(3, empty($voterName) ? null : $voterName);
        $stmt->bindValue(4, strtoupper(trim($barangayName)));
        $stmt->bindValue(5, empty($barangayName) ? null : $barangayName);
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['imgUrl'] = $imgUrl . self::ACTIVE_PROJECT . '_' . $row['generated_id_no'] . '?' . strtotime((new \DateTime())->format('Y-m-d H:i:s'));
            $row['cellphone_no'] = $row['cellphone'];
            $data[] = $row;
        }

        foreach ($data as &$row) {
            //$lgc = $this->getLGC($row['municipality_no'], $row['brgy_no']);
            $row['lgc'] = [
                'voter_name' => '- disabled -',
                //$lgc['voter_name'],
                'cellphone' => '- disabled -' //$lgc['cellphone']
            ];
        }

        return new JsonResponse([
            "data" => $data,
            "totalExpected" => $summary['total_expected'],
            "totalAttended" => $summary['total_attended'],
            "totalLgc" => $summary['total_lgc'],
            'totalLgo' => $summary['total_lgo'],
            "totalLopp" => $summary['total_lopp'],
            "totalLppp" => $summary['total_lppp'],
            "totalLppp1" => $summary['total_lppp1'],
            'totalLppp2' => $summary['total_lppp2'],
            'totalLppp3' => $summary['total_lppp3'],
            'totalJpm' => $summary['total_jpm']
        ]);
    }

    /**
     * @Route("/ajax_m_get_active_event_attendees_summary",
     *       name="ajax_m_get_active_event_attendees_summary",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetActiveEventAttendeesSummary(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $imgUrl = $this->getParameter('img_url');

        $batchSize = 5;
        $batchNo = $request->get("batchNo");
        $voterName = $request->get("voterName");
        $eventId = $request->get('eventId');
        $barangayName = $request->get('barangayName');

        $event = $em->getRepository("AppBundle:ProjectEventHeader")->findOneBy([
            'eventId' => $eventId,
            'status' => self::ACTIVE_STATUS,
        ]);


        if (!$event) {
            return new JsonResponse(null, 404);
        }

        $sql = "SELECT
        COALESCE(SUM( CASE WHEN ed.has_attended = 1 THEN 1 ELSE 0 END),0) AS total_attended,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' THEN 1 ELSE 0 END),0) AS total_lgc,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' THEN 1 ELSE 0 END),0) AS total_lgo,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' THEN 1 ELSE 0 END),0) AS total_lopp,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' THEN 1 ELSE 0 END),0) AS total_lppp,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' THEN 1 ELSE 0 END),0) AS total_lppp1,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' THEN 1 ELSE 0 END),0) AS total_lppp2,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' THEN 1 ELSE 0 END),0) AS total_lppp3,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' THEN 1 ELSE 0 END),0) AS total_jpm,
        COALESCE(SUM( CASE WHEN pv.voter_group IS NULL OR pv.voter_group = '' THEN 1 ELSE 0 END),0) AS total_no_position,
        COALESCE(COUNT(ed.event_detail_id),0) AS total_expected
        FROM tbl_project_event_detail ed INNER JOIN tbl_project_voter pv 
        ON pv.pro_voter_id = ed.pro_voter_id 
        WHERE ed.event_id = ? AND (pv.barangay_name = ? OR ? IS NULL )";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $eventId);
        $stmt->bindValue(2, strtoupper(trim($barangayName)));
        $stmt->bindValue(3, empty($barangayName) ? null : $barangayName);
        $stmt->execute();

        $summary = $stmt->fetch(\PDO::FETCH_ASSOC);

        return new JsonResponse([
            "totalExpected" => $summary['total_expected'],
            "totalAttended" => $summary['total_attended'],
            "totalLgc" => $summary['total_lgc'],
            'totalLgo' => $summary['total_lgo'],
            "totalLopp" => $summary['total_lopp'],
            "totalLppp" => $summary['total_lppp'],
            "totalLppp1" => $summary['total_lppp1'],
            'totalLppp2' => $summary['total_lppp2'],
            'totalLppp3' => $summary['total_lppp3'],
            'totalJpm' => $summary['total_jpm'],
            'totalNoPosition' => $summary['total_no_position']
        ]);
    }

    /**
     * @Route("/ajax_m_get_jpm_province_summary",
     *       name="ajax_m_get_jpm_province_summary",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetJpmProvinceSummary(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT 
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' THEN 1 ELSE 0 END),0) AS total_lgc,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' THEN 1 ELSE 0 END),0) AS total_lgo,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' THEN 1 ELSE 0 END),0) AS total_lopp,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' THEN 1 ELSE 0 END),0) AS total_lppp,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' THEN 1 ELSE 0 END),0) AS total_lppp1,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' THEN 1 ELSE 0 END),0) AS total_lppp2,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' THEN 1 ELSE 0 END),0) AS total_lppp3,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' THEN 1 ELSE 0 END),0) AS total_jpm,
            
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' AND pv.is_non_voter = 1 THEN 1 ELSE 0 END),0) AS total_lgc_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lgo_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lopp_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp1_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp2_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp3_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_jpm_non_voter,
            
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' AND pv.has_id = 1 THEN 1 ELSE 0 END),0) AS total_lgc_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lgo_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lopp_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp1_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp2_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp3_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_jpm_has_id,
            (SELECT COALESCE(SUM(m.total_precincts),0)  FROM psw_municipality m WHERE m.province_code = 53 AND m.municipality_no <> 16 ) AS total_precincts
            FROM tbl_project_voter pv
            WHERE pv.elect_id = ? AND pro_id = ? AND pv.has_id = 1 AND  pv.voter_group IN ('LGC','LGO','LOPP','LPPP','LPPP1','LPPP2','LPPP3','JPM')";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, self::ACTIVE_ELECTION);
        $stmt->bindValue(2, 3);
        $stmt->execute();

        $summary = $stmt->fetch(\PDO::FETCH_ASSOC);

        return new JsonResponse([
            "totalLgc" => $summary['total_lgc'],
            'totalLgo' => $summary['total_lgo'],
            "totalLopp" => $summary['total_lopp'],
            "totalLppp" => $summary['total_lppp'],
            "totalLppp1" => $summary['total_lppp1'],
            'totalLppp2' => $summary['total_lppp2'],
            'totalLppp3' => $summary['total_lppp3'],
            'totalJpm' => $summary['total_jpm'],

            "totalLgcHasId" => $summary['total_lgc_has_id'],
            'totalLgoHasId' => $summary['total_lgo_has_id'],
            "totalLoppHasId" => $summary['total_lopp_has_id'],
            "totalLpppHasId" => $summary['total_lppp_has_id'],
            "totalLppp1HasId" => $summary['total_lppp1_has_id'],
            'totalLppp2HasId' => $summary['total_lppp2_has_id'],
            'totalLppp3HasId' => $summary['total_lppp3_has_id'],
            'totalJpmHasId' => $summary['total_jpm_has_id'],

            "totalLgcNonVoter" => $summary['total_lgc_non_voter'],
            'totalLgoNonVoter' => $summary['total_lgo_non_voter'],
            "totalLoppNonVoter" => $summary['total_lopp_non_voter'],
            "totalLpppNonVoter" => $summary['total_lppp_non_voter'],
            "totalLppp1NonVoter" => $summary['total_lppp1_non_voter'],
            'totalLppp2NonVoter' => $summary['total_lppp2_non_voter'],
            'totalLppp3NonVoter' => $summary['total_lppp3_non_voter'],
            'totalJpmNonVoter' => $summary['total_jpm_non_voter'],
            'totalPrecincts' => $summary['total_precincts']
        ]);
    }

    /**
     * @Route("/ajax_m_get_jpm_district_summary/{district}",
     *       name="ajax_m_get_jpm_district_summary",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetJpmDistrictSummary($district, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT 
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' THEN 1 ELSE 0 END),0) AS total_lgc,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' THEN 1 ELSE 0 END),0) AS total_lgo,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' THEN 1 ELSE 0 END),0) AS total_lopp,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' THEN 1 ELSE 0 END),0) AS total_lppp,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' THEN 1 ELSE 0 END),0) AS total_lppp1,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' THEN 1 ELSE 0 END),0) AS total_lppp2,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' THEN 1 ELSE 0 END),0) AS total_lppp3,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' THEN 1 ELSE 0 END),0) AS total_jpm,
            
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' AND pv.is_non_voter = 1 THEN 1 ELSE 0 END),0) AS total_lgc_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lgo_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lopp_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp1_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp2_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp3_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_jpm_non_voter,
            
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' AND pv.has_id = 1 THEN 1 ELSE 0 END),0) AS total_lgc_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lgo_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lopp_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp1_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp2_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp3_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_jpm_has_id,
            (SELECT COALESCE(SUM(m2.total_precincts),0)  FROM psw_municipality m2 WHERE m2.province_code = 53 AND m2.municipality_no <> 16 AND m2.district = m.district ) AS total_precincts
            FROM tbl_project_voter pv
            INNER JOIN psw_municipality m on m.municipality_no = pv.municipality_no AND m.province_code = 53
            WHERE pv.elect_id = ? AND pv.pro_id = ? AND m.district = ? AND pv.has_id = 1 AND pv.voter_group IN ('LGC','LGO','LOPP','LPPP','LPPP1','LPPP2','LPPP3','JPM')";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, self::ACTIVE_ELECTION);
        $stmt->bindValue(2, 3);
        $stmt->bindValue(3, $district);
        $stmt->execute();

        $summary = $stmt->fetch(\PDO::FETCH_ASSOC);

        return new JsonResponse([
            "totalLgc" => $summary['total_lgc'],
            'totalLgo' => $summary['total_lgo'],
            "totalLopp" => $summary['total_lopp'],
            "totalLppp" => $summary['total_lppp'],
            "totalLppp1" => $summary['total_lppp1'],
            'totalLppp2' => $summary['total_lppp2'],
            'totalLppp3' => $summary['total_lppp3'],
            'totalJpm' => $summary['total_jpm'],

            "totalLgcHasId" => $summary['total_lgc_has_id'],
            'totalLgoHasId' => $summary['total_lgo_has_id'],
            "totalLoppHasId" => $summary['total_lopp_has_id'],
            "totalLpppHasId" => $summary['total_lppp_has_id'],
            "totalLppp1HasId" => $summary['total_lppp1_has_id'],
            'totalLppp2HasId' => $summary['total_lppp2_has_id'],
            'totalLppp3HasId' => $summary['total_lppp3_has_id'],
            'totalJpmHasId' => $summary['total_jpm_has_id'],

            "totalLgcNonVoter" => $summary['total_lgc_non_voter'],
            'totalLgoNonVoter' => $summary['total_lgo_non_voter'],
            "totalLoppNonVoter" => $summary['total_lopp_non_voter'],
            "totalLpppNonVoter" => $summary['total_lppp_non_voter'],
            "totalLppp1NonVoter" => $summary['total_lppp1_non_voter'],
            'totalLppp2NonVoter' => $summary['total_lppp2_non_voter'],
            'totalLppp3NonVoter' => $summary['total_lppp3_non_voter'],
            'totalJpmNonVoter' => $summary['total_jpm_non_voter'],
            'totalPrecincts' => $summary['total_precincts']
        ]);
    }

    /**
     * @Route("/ajax_m_get_jpm_municipality_summary/{municipalityName}",
     *       name="ajax_m_get_jpm_municipality_summary",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetJpmMunicipalitySummary(Request $request, $municipalityName)
    {
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT 
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' THEN 1 ELSE 0 END),0) AS total_lgc,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' THEN 1 ELSE 0 END),0) AS total_lgo,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' THEN 1 ELSE 0 END),0) AS total_lopp,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' THEN 1 ELSE 0 END),0) AS total_lppp,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' THEN 1 ELSE 0 END),0) AS total_lppp1,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' THEN 1 ELSE 0 END),0) AS total_lppp2,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' THEN 1 ELSE 0 END),0) AS total_lppp3,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' THEN 1 ELSE 0 END),0) AS total_jpm,
            
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' AND pv.is_non_voter = 1 THEN 1 ELSE 0 END),0) AS total_lgc_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lgo_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lopp_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp1_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp2_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp3_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_jpm_non_voter,
            
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' AND pv.has_id = 1 THEN 1 ELSE 0 END),0) AS total_lgc_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lgo_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lopp_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp1_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp2_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp3_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_jpm_has_id,
            (SELECT m2.total_precincts  FROM psw_municipality m2 WHERE m2.province_code = 53 AND m2.municipality_no = pv.municipality_no ) AS total_precincts 
            FROM tbl_project_voter pv
            WHERE pv.elect_id = ? 
            AND pv.pro_id = ?
            AND pv.municipality_name = ? 
            AND pv.has_id = 1
            AND pv.voter_group IN ('LGC','LGO','LOPP','LPPP','LPPP1','LPPP2','LPPP3','JPM')";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, self::ACTIVE_ELECTION);
        $stmt->bindValue(2, 3);
        $stmt->bindValue(3, $municipalityName);
        $stmt->execute();

        $summary = $stmt->fetch(\PDO::FETCH_ASSOC);

        return new JsonResponse([
            "totalLgc" => $summary['total_lgc'],
            'totalLgo' => $summary['total_lgo'],
            "totalLopp" => $summary['total_lopp'],
            "totalLppp" => $summary['total_lppp'],
            "totalLppp1" => $summary['total_lppp1'],
            'totalLppp2' => $summary['total_lppp2'],
            'totalLppp3' => $summary['total_lppp3'],
            'totalJpm' => $summary['total_jpm'],

            "totalLgcHasId" => $summary['total_lgc_has_id'],
            'totalLgoHasId' => $summary['total_lgo_has_id'],
            "totalLoppHasId" => $summary['total_lopp_has_id'],
            "totalLpppHasId" => $summary['total_lppp_has_id'],
            "totalLppp1HasId" => $summary['total_lppp1_has_id'],
            'totalLppp2HasId' => $summary['total_lppp2_has_id'],
            'totalLppp3HasId' => $summary['total_lppp3_has_id'],
            'totalJpmHasId' => $summary['total_jpm_has_id'],

            "totalLgcNonVoter" => $summary['total_lgc_non_voter'],
            'totalLgoNonVoter' => $summary['total_lgo_non_voter'],
            "totalLoppNonVoter" => $summary['total_lopp_non_voter'],
            "totalLpppNonVoter" => $summary['total_lppp_non_voter'],
            "totalLppp1NonVoter" => $summary['total_lppp1_non_voter'],
            'totalLppp2NonVoter' => $summary['total_lppp2_non_voter'],
            'totalLppp3NonVoter' => $summary['total_lppp3_non_voter'],
            'totalJpmNonVoter' => $summary['total_jpm_non_voter'],
            'totalPrecincts' => $summary['total_precincts']
        ]);
    }

    /**
     * @Route("/ajax_m_get_jpm_barangay_summary/{municipalityName}/{barangayName}",
     *       name="ajax_m_get_jpm_barangay_summary",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetJpmBarangaySummary(Request $request, $municipalityName, $barangayName)
    {
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT 
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' THEN 1 ELSE 0 END),0) AS total_lgc,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' THEN 1 ELSE 0 END),0) AS total_lgo,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' THEN 1 ELSE 0 END),0) AS total_lopp,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' THEN 1 ELSE 0 END),0) AS total_lppp,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' THEN 1 ELSE 0 END),0) AS total_lppp1,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' THEN 1 ELSE 0 END),0) AS total_lppp2,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' THEN 1 ELSE 0 END),0) AS total_lppp3,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' THEN 1 ELSE 0 END),0) AS total_jpm,
            
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' AND pv.is_non_voter = 1 THEN 1 ELSE 0 END),0) AS total_lgc_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lgo_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lopp_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp1_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp2_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp3_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_jpm_non_voter,
            
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' AND pv.has_id = 1 THEN 1 ELSE 0 END),0) AS total_lgc_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lgo_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lopp_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp1_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp2_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp3_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_jpm_has_id,
            (
                SELECT SUM(b.total_precincts)
                FROM 
                psw_barangay b INNER JOIN 
                psw_municipality m ON m.municipality_code = b.municipality_code AND m.province_code = 53
                WHERE 
                m.name = ? AND b.name = ?
            ) AS total_precincts

            FROM tbl_project_voter pv
            WHERE pv.elect_id = ? 
            AND pv.pro_id = ?
            AND pv.municipality_name = ?  
            AND pv.barangay_name = ?
            AND pv.has_id = 1
            AND pv.voter_group IN ('LGC','LGO','LOPP','LPPP','LPPP1','LPPP2','LPPP3','JPM')";

        $stmt = $em->getConnection()->prepare($sql);

        $stmt->bindValue(1, $municipalityName);
        $stmt->bindValue(2, $barangayName);
        $stmt->bindValue(3, self::ACTIVE_ELECTION);
        $stmt->bindValue(4, 3);
        $stmt->bindValue(5, $municipalityName);
        $stmt->bindValue(6, $barangayName);
        $stmt->execute();

        $summary = $stmt->fetch(\PDO::FETCH_ASSOC);

        return new JsonResponse([
            "totalLgc" => $summary['total_lgc'],
            'totalLgo' => $summary['total_lgo'],
            "totalLopp" => $summary['total_lopp'],
            "totalLppp" => $summary['total_lppp'],
            "totalLppp1" => $summary['total_lppp1'],
            'totalLppp2' => $summary['total_lppp2'],
            'totalLppp3' => $summary['total_lppp3'],
            'totalJpm' => $summary['total_jpm'],

            "totalLgcHasId" => $summary['total_lgc_has_id'],
            'totalLgoHasId' => $summary['total_lgo_has_id'],
            "totalLoppHasId" => $summary['total_lopp_has_id'],
            "totalLpppHasId" => $summary['total_lppp_has_id'],
            "totalLppp1HasId" => $summary['total_lppp1_has_id'],
            'totalLppp2HasId' => $summary['total_lppp2_has_id'],
            'totalLppp3HasId' => $summary['total_lppp3_has_id'],
            'totalJpmHasId' => $summary['total_jpm_has_id'],

            "totalLgcNonVoter" => $summary['total_lgc_non_voter'],
            'totalLgoNonVoter' => $summary['total_lgo_non_voter'],
            "totalLoppNonVoter" => $summary['total_lopp_non_voter'],
            "totalLpppNonVoter" => $summary['total_lppp_non_voter'],
            "totalLppp1NonVoter" => $summary['total_lppp1_non_voter'],
            'totalLppp2NonVoter' => $summary['total_lppp2_non_voter'],
            'totalLppp3NonVoter' => $summary['total_lppp3_non_voter'],
            'totalJpmNonVoter' => $summary['total_jpm_non_voter'],
            'totalPrecincts' => $summary['total_precincts']
        ]);
    }


    /**
     * @Route("/ajax_m_active_event_attendees_summary_by_position",
     *       name="ajax_m_active_event_attendees_summary_by_position",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetActiveEventAttendeesSummaryPosition(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $eventId = $request->get('eventId');
        $event = $em->getRepository("AppBundle:ProjectEventHeader")->findOneBy([
            'status' => self::ACTIVE_STATUS,
        ]);

        if (!$event) {
            return new JsonResponse(null, 404);
        }

        $sql = "SELECT
        COALESCE(SUM( CASE WHEN ed.has_attended = 1 THEN 1 ELSE 0 END),0) AS total_attended,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' THEN 1 ELSE 0 END),0) AS total_lgc,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' THEN 1 ELSE 0 END),0) AS total_lgo,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' THEN 1 ELSE 0 END),0) AS total_lopp,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' THEN 1 ELSE 0 END),0) AS total_lppp,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' THEN 1 ELSE 0 END),0) AS total_lppp1,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' THEN 1 ELSE 0 END),0) AS total_lppp2,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' THEN 1 ELSE 0 END),0) AS total_lppp3,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' THEN 1 ELSE 0 END),0) AS total_jpm,
        COALESCE(COUNT(ed.event_detail_id),0) AS total_expected
        FROM tbl_project_event_detail ed INNER JOIN tbl_project_voter pv 
        ON pv.pro_voter_id = ed.pro_voter_id 
        WHERE ed.event_id = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $event->getEventId());
        $stmt->execute();

        $summary = $stmt->fetch(\PDO::FETCH_ASSOC);

        return new JsonResponse([
            "totalExpected" => $summary['total_expected'],
            "totalAttended" => $summary['total_attended'],
            "totalLgc" => $summary['total_lgc'],
            'totalLgo' => $summary['total_lgo'],
            "totalLopp" => $summary['total_lopp'],
            "totalLppp" => $summary['total_lppp'],
            "totalLppp1" => $summary['total_lppp1'],
            'totalLppp2' => $summary['total_lppp2'],
            'totalLppp3' => $summary['total_lppp3'],
            'totalJpm' => $summary['total_jpm']
        ]);
    }

    /**
     * @Route("/ajax_m_active_event_attendees_summary_by_barangay",
     *       name="ajax_m_active_event_attendees_summary_by_barangay",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetActiveEventAttendeesSummaryByBarangay(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $eventId = $request->get('eventId');
        $event = $em->getRepository("AppBundle:ProjectEventHeader")->findOneBy([
            'status' => self::ACTIVE_STATUS,
        ]);

        if (!$event) {
            return new JsonResponse(null, 404);
        }

        $sql = "SELECT
                COALESCE(SUM( CASE WHEN ed.has_attended = 1 THEN 1 ELSE 0 END),0) AS total_attended,
                COALESCE(COUNT(ed.event_detail_id),0) AS total_expected,
                COALESCE(COUNT(pv.pro_voter_id),0) AS total_attendees_per_barangay,
                COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' THEN 1 ELSE 0 END),0) AS total_lgc,
                COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' THEN 1 ELSE 0 END),0) AS total_lgo,
                COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' THEN 1 ELSE 0 END),0) AS total_lopp,
                COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' THEN 1 ELSE 0 END),0) AS total_lppp,
                COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' THEN 1 ELSE 0 END),0) AS total_lppp1,
                COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' THEN 1 ELSE 0 END),0) AS total_lppp2,
                COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' THEN 1 ELSE 0 END),0) AS total_lppp3,
                COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' THEN 1 ELSE 0 END),0) AS total_jpm,
                pv.barangay_name,
                pv.municipality_name
                FROM tbl_project_event_detail ed INNER JOIN tbl_project_voter pv 
                ON pv.pro_voter_id = ed.pro_voter_id 
                WHERE ed.event_id = ? AND pv.voter_group IS NOT NULL AND pv.voter_group <> ''
                GROUP BY pv.municipality_no, pv.brgy_no
                ORDER BY pv.municipality_name, pv.barangay_name ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $event->getEventId());
        $stmt->execute();

        $summary = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return new JsonResponse($summary);
    }


    /**
     * @Route("/ajax_m_get_active_event_expected_attendees",
     *       name="ajax_m_active_event_expected_attendees",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetActiveEventExpectedAttendees(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $imgUrl = $this->getParameter('img_url');

        $batchSize = 5;
        $batchNo = $request->get("batchNo");
        $voterName = $request->get("voterName");
        $eventId = $request->get('eventId');
        $displayAll = $request->get('displayAll');

        $batchOffset = $batchNo * $batchSize;

        $event = $em->getRepository("AppBundle:ProjectEventHeader")->findOneBy([
            'eventId' => $eventId,
            'status' => 'A',
        ]);

        if (!$event) {
            return new JsonResponse(null, 404);
        }

        $sql = "SELECT
        COALESCE(SUM( CASE WHEN ed.has_attended = 1 THEN 1 ELSE 0 END),0) AS total_attended,
        COALESCE(COUNT(ed.event_detail_id),0) AS total_expected
        FROM tbl_project_event_detail ed WHERE ed.event_id = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $eventId);
        $stmt->execute();

        $summary = $stmt->fetch(\PDO::FETCH_ASSOC);

        $sql = "SELECT pv.*
        FROM tbl_project_event_detail ed
        INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = ed.pro_voter_id
        WHERE ed.event_id  = ? ";

        if (!is_numeric($voterName)) {
            $sql .= " AND (pv.voter_name LIKE ? OR ? IS NULL ) ";
        } else {
            $sql .= " AND (pv.pro_id_code LIKE ? OR ? IS NULL ) ";
        }

        if ($displayAll == 0) {
            $sql .= " AND (pv.has_photo = 0 OR pv.has_photo IS NULL) ";
        }

        $sql .= " ORDER BY pv.voter_name ASC LIMIT {$batchSize} OFFSET {$batchOffset}";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $eventId);
        $stmt->bindValue(2, '%' . strtoupper(trim($voterName)) . '%');
        $stmt->bindValue(3, empty($voterName) ? null : $voterName);
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['imgUrl'] = $imgUrl . self::ACTIVE_PROJECT . '_' . $row['pro_id_code'] . '?' . strtotime((new \DateTime())->format('Y-m-d H:i:s'));
            $row['cellphone_no'] = $row['cellphone'];
            $data[] = $row;
        }

        return new JsonResponse([
            "data" => $data,
            "totalExpected" => $summary['total_expected'],
            "totalAttended" => $summary['total_attended'],
        ]);
    }

    /**
     * @Route("/ajax_m_get_active_event_claimed_attendees",
     *       name="ajax_m_active_event_claimed_attendees",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetActiveEventClaimedAttendees(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $imgUrl = $this->getParameter('img_url');

        $batchSize = 3;
        $batchNo = $request->get("batchNo");
        $voterName = $request->get("voterName");
        $eventId = $request->get('eventId');

        $batchOffset = $batchNo * $batchSize;

        $event = $em->getRepository("AppBundle:ProjectEventHeader")->findOneBy([
            'eventId' => $eventId,
            'status' => 'A',
        ]);

        if (!$event) {
            return new JsonResponse(null, 404);
        }

        $sql = "SELECT
        COALESCE(SUM( CASE WHEN ed.has_attended = 1 THEN 1 ELSE 0 END),0) AS total_expected,
        COALESCE(SUM( CASE WHEN ed.has_claimed = 1 THEN 1 ELSE 0 END),0) AS total_claimed
        FROM tbl_project_event_detail ed WHERE ed.event_id = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $eventId);
        $stmt->execute();

        $summary = $stmt->fetch(\PDO::FETCH_ASSOC);

        $sql = "SELECT pv.*
        FROM tbl_project_event_detail ed
        INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = ed.pro_voter_id
        WHERE ed.event_id  = ? AND ed.has_claimed = 1 AND (pv.voter_name LIKE ? OR ? IS NULL ) ORDER BY ed.claimed_at DESC LIMIT {$batchSize} OFFSET {$batchOffset}";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $eventId);
        $stmt->bindValue(2, '%' . strtoupper(trim($voterName)) . '%');
        $stmt->bindValue(3, empty($voterName) ? null : $voterName);
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['imgUrl'] = $imgUrl . self::ACTIVE_PROJECT . '_' . $row['generated_id_no'] . '?' . strtotime((new \DateTime())->format('Y-m-d H:i:s'));
            $row['cellphone_no'] = $row['cellphone'];
            $data[] = $row;
        }

        return new JsonResponse([
            "data" => $data,
            "totalExpected" => $summary['total_expected'],
            "totalClaimed" => $summary['total_claimed'],
        ]);
    }

    private function getBarangay($municipalityCode, $brgyNo)
    {
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT * FROM psw_barangay b WHERE b.municipality_code = ? AND b.brgy_no = ? ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipalityCode);
        $stmt->bindValue(2, $brgyNo);
        $stmt->execute();

        $barangay = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $barangay;
    }

    /**
     * @Route("/ajax_m_get_jpm_municipalities",
     *       name="ajax_m_get_jpm_municipalities",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function getJpmMunicipalities()
    {
        return new JsonResponse($this->getMunicipalities(53));
    }

    private function getMunicipalities($provinceCode)
    {
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT * FROM psw_municipality m WHERE m.province_code = ? ORDER BY m.name ASC";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->execute();

        $municipalities = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $municipalities[] = $row;
        }

        if (empty($municipalities)) {
            $municipalities = [];
        }

        return $municipalities;
    }

    /**
     * @Route("/ajax_m_get_jpm_districts",
     *       name="ajax_m_get_jpm_districts",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function getJpmDistricts()
    {
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT DISTINCT district FROM psw_municipality m WHERE m.province_code = ? ORDER BY m.district ASC";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, 53);
        $stmt->execute();

        $districts = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $districts[] = $row;
        }

        if (empty($districts)) {
            $districts = [];
        }

        return new JsonResponse($districts);
    }

    /**
     * @Route("/ajax_m_get_jpm_barangays/{municipalityName}",
     *       name="ajax_m_get_jpm_barangays",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetJpmBarangays(Request $request, $municipalityName)
    {

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT b.* FROM psw_barangay b 
                INNER JOIN psw_municipality m ON m.municipality_code = b.municipality_code AND m.province_code = 53
                WHERE m.name = ? ORDER BY b.name ASC";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipalityName);

        $stmt->execute();
        $barangays = $stmt->fetchAll();

        if (count($barangays) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($barangays);
    }


    /**
     * @Route("/ajax_m_get_active_event_new_attendees",
     *       name="ajax_m_active_event_new_attendees",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetActiveEventNewAttendees(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $imgUrl = $this->getParameter('img_url');

        $batchSize = 3;
        $batchNo = $request->get("batchNo");
        $voterName = $request->get("voterName");
        $eventId = $request->get('eventId');

        $batchOffset = $batchNo * $batchSize;

        $event = $em->getRepository("AppBundle:ProjectEventHeader")->findOneBy([
            'eventId' => $eventId,
            'status' => 'A',
        ]);

        if (!$event) {
            return new JsonResponse(null, 404);
        }

        $sql = "SELECT COALESCE(COUNT(ed.event_detail_id)) FROM tbl_project_event_detail ed WHERE ed.event_id = ? AND ed.has_attended = ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $eventId);
        $stmt->bindValue(2, 1);
        $stmt->execute();

        $totalExpected = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(ed.event_detail_id)) FROM tbl_project_event_detail ed WHERE ed.event_id = ? AND ed.has_new_id = ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $eventId);
        $stmt->bindValue(2, 1);
        $stmt->execute();

        $totalNewMember = $stmt->fetchColumn();

        $sql = "SELECT v.*
        FROM tbl_project_event_detail ed
        INNER JOIN tbl_project_voter v ON v.voter_id = ed.voter_id
        WHERE ed.event_id  = ? AND ed.has_new_id = 1 AND (v.voter_name LIKE ? OR ? IS NULL ) ORDER BY ed.verify_at DESC LIMIT {$batchSize} OFFSET {$batchOffset}";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $eventId);
        $stmt->bindValue(2, '%' . strtoupper(trim($voterName)) . '%');
        $stmt->bindValue(3, empty($voterName) ? null : $voterName);
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['imgUrl'] = $imgUrl . '3_' . $row['pro_id_code'] . '?' . strtotime((new \DateTime())->format('Y-m-d H:i:s'));
            $row['cellphone_no'] = $row['cellphone'];
            $data[] = $row;
        }

        return new JsonResponse([
            "data" => $data,
            "totalExpected" => $totalExpected,
            "totalNewMember" => $totalNewMember,
        ]);
    }

    /**
     * @Route("/ajax_m_post_event_attendee",
     *       name="ajax_m_post_attendee",
     *        options={ "expose" = true }
     * )
     * @Method("POST")
     */

    public function ajaxPostEventAttendee(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $data = json_decode($request->getContent(), true);
        $request->request->replace($data);
        $eventId = $request->get('eventId');
        $proVoterId = $request->get("proVoterId");

        $event = $em->getRepository("AppBundle:ProjectEventHeader")->findOneBy([
            'eventId' => $eventId,
            'status' => 'A',
        ]);

        $eventDetail = $em->getRepository("AppBundle:ProjectEventDetail")->findOneBy([
            'proVoterId' => $proVoterId,
            'eventId' => $eventId,
        ]);

        $projectVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy(['proVoterId' => $proVoterId]);

        if (!$event || !$projectVoter) {
            return new JsonResponse(['message' => 'Event not found. Please contact the system administrator.'], 400);
        }

        if (!$eventDetail) {

            if ($projectVoter->getStatus() != 'A') {
                return new JsonResponse(['message' => "Opps! Action denied... Voter either blocked or deactivated..."], 400);
            }

            $entity = new ProjectEventDetail();
            $entity->setProVoterId($proVoterId);
            $entity->setEventId($eventId);
            $entity->setProId(3);
            $entity->setHasAttended(1);
            $entity->setHasClaimed(0);
            $entity->setHasNewId(0);
            $entity->setCreatedAt(new \DateTime());
            $entity->setCreatedBy('android_app');
            $entity->setAttendedAt(new \DateTime());
            $em->persist($entity);

        } else {

            if ($eventDetail->getHasAttended()) {
                return new JsonResponse(['message' => 'Opps! Attendee already registered'], 400);
            }

            $eventDetail->setHasAttended(1);
            $eventDetail->setAttendedAt(new \DateTime());
        }

        $em->flush();
        $em->clear();

        return new JsonResponse(null);
    }

    /**
     * @Route("/ajax_m_post_event_attendee_claim_turon",
     *       name="ajax_m_post_event_attendee_claim_turon",
     *        options={ "expose" = true }
     * )
     * @Method("POST")
     */

    public function ajaxPostEventAttendeeClaimToron(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $data = json_decode($request->getContent(), true);
        $request->request->replace($data);
        $eventId = $request->get('eventId');
        $proVoterId = $request->get("proVoterId");

        $event = $em->getRepository("AppBundle:ProjectEventHeader")->findOneBy([
            'eventId' => $eventId,
            'status' => 'A',
        ]);

        $eventDetail = $em->getRepository("AppBundle:ProjectEventDetail")->findOneBy([
            'proVoterId' => $proVoterId,
            'eventId' => $eventId,
        ]);

        if (!$event) {
            return new JsonResponse(['message' => 'Event not found. Please contact the system administrator.'], 400);
        }

        if (!$eventDetail) {
            $entity = new ProjectEventDetail();
            $entity->setProVoterId($proVoterId);
            $entity->setEventId($eventId);
            $entity->setProId(3);
            $entity->setHasClaimed(1);
            $entity->setHasNewId(0);
            $entity->setCreatedAt(new \DateTime());
            $entity->setCreatedBy('android_app');
            $entity->setAttendedAt(new \DateTime());
            $entity->setClaimedAt(new \DateTime());
            $em->persist($entity);

        } else {
            if ($eventDetail->getHasClaimed()) {
                return new JsonResponse(['message' => 'Opps! Ang ID na ito ay na scan na. Maaring na claim na ang pamasahe. Please contact the system administrator.'], 400);
            }

            $eventDetail->setHasClaimed(1);
            $eventDetail->setClaimedAt(new \DateTime());
        }

        $em->flush();
        $em->clear();

        return new JsonResponse(null);
    }

    /**
     * @Route("/ajax_m_post_event_attendee_cancel_claim",
     *       name="ajax_m_post_event_attendee_cancel_claim",
     *        options={ "expose" = true }
     * )
     * @Method("POST")
     */

    public function ajaxPostEventAttendeeCancelClaim(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $data = json_decode($request->getContent(), true);
        $request->request->replace($data);
        $eventId = $request->get('eventId');
        $proVoterId = $request->get("proVoterId");

        $event = $em->getRepository("AppBundle:ProjectEventHeader")->findOneBy([
            'eventId' => $eventId,
            'status' => 'A',
        ]);

        $eventDetail = $em->getRepository("AppBundle:ProjectEventDetail")->findOneBy([
            'proVoterId' => $proVoterId,
            'eventId' => $eventId,
        ]);

        if (!$event) {
            return new JsonResponse(['message' => 'Event not found. Please contact the system administrator.'], 400);
        }

        $em->remove($eventDetail);
        $em->flush();
        $em->clear();

        return new JsonResponse(null);
    }

    /**
     * @Route("/ajax_m_post_event_new_attendee",
     *       name="ajax_m_post_event_new_attendee",
     *        options={ "expose" = true }
     * )
     * @Method("POST")
     */

    public function ajaxPostEventNewAttendee(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $data = json_decode($request->getContent(), true);
        $request->request->replace($data);
        $eventId = $request->get('eventId');
        $proVoterId = $request->get("proVoterId");
        $voterId = $request->get('voterId');

        $event = $em->getRepository("AppBundle:ProjectEventHeader")->findOneBy([
            'eventId' => $eventId,
            'status' => 'A',
        ]);

        $eventDetail = $em->getRepository("AppBundle:ProjectEventDetail")->findOneBy([
            'proVoterId' => $proVoterId,
            'eventId' => $eventId,
        ]);

        if (!$event) {
            return new JsonResponse(['message' => 'Event not found. Please contact the system administrator.'], 400);
        }

        if (!$eventDetail) {
            $entity = new ProjectEventDetail();
            $entity->setProVoterId($proVoterId);
            $entity->setEventId($eventId);
            $entity->setProId(2);
            $entity->setHasAttended(1);
            $entity->setHasNewId(1);
            $entity->setHasClaimed(0);
            $entity->setCreatedAt(new \DateTime());
            $entity->setCreatedBy('android_app');
            $entity->setAttendedAt(new \DateTime());
            $entity->setVerifyAt(new \DateTime());
            $em->persist($entity);

        } else {
            if ($eventDetail->getHasNewId()) {
                return new JsonResponse(['message' => 'Opps! Attendee\'s ID already been verified.'], 400);
            }

            $eventDetail->setHasNewId(1);
            $eventDetail->setHasAttended(1);
            $eventDetail->setVerifyAt(new \DateTime());
        }

        $em->flush();
        $em->clear();

        return new JsonResponse(null);
    }

    /**
     * @Route("/ajax_m_get_project_voter_groups/{proId}",
     *       name="ajax_m_get_project_voter_groups",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetProjectVoterGroups($proId)
    {
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT DISTINCT voter_group FROM tbl_project_voter
                WHERE voter_group IS NOT NULL AND voter_group <> '' AND pro_id = ? ORDER BY voter_group ASC";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $proId);
        $stmt->execute();

        $voterGroups = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $voterGroups[] = $row['voter_group'];
        }

        return new JsonResponse($voterGroups);
    }

    /**
     * @Route("/ajax_m_patch_project_voter/{proId}/{proVoterId}",
     *     name="ajax_m_patch_project_voter",
     *    options={"expose" = true}
     * )
     * @Method("PATCH")
     */

    public function ajaxPatchProjectVoterAction($proId, $proVoterId, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $data = json_decode($request->getContent(), true);
        $request->request->replace($data);

        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy([
            'proId' => $proId,
            'proVoterId' => $proVoterId,
        ]);

        $proVoter->setCellphone($request->get('cellphone'));
        $proVoter->setVoterGroup($request->get('voterGroup'));
        $proVoter->setUpdatedAt(new \DateTime());
        $proVoter->setUpdatedBy('android_app');
        $proVoter->setStatus('A');

        $validator = $this->get('validator');
        $violations = $validator->validate($proVoter);

        $errors = [];

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse($errors, 400);
        }

        $em->flush();

        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($proVoter));
    }

    /**
     * @Route("/ajax_upload_m_project_voter_photo/{proId}/{proVoterId}",
     *     name="ajax_upload_m_project_voter_photo",
     *     options={"expose" = true}
     *     )
     * @Method("POST")
     */

    public function ajaxUploadProjectVoterPhoto(Request $request, $proId, $proVoterId)
    {
        $em = $this->getDoctrine()->getManager();

        $projectVoter = $em->getRepository("AppBundle:ProjectVoter")
            ->findOneBy(['proId' => $proId, 'proVoterId' => $proVoterId]);

        if (!$projectVoter) {
            return new JsonResponse(['message' => 'not found'], 404);
        }

        if ($projectVoter->getGeneratedIdNo() == null || $projectVoter->getGeneratedIdNo() == '')
            return new JsonResponse(['message' => 'Please generate id'], 400);

        $serializer = $this->get('serializer');

        $images = $request->files->get('files');
        $filename = $proId . '_' . $projectVoter->getGeneratedIdNo() . '.jpg';
        $imgRoot = __DIR__ . '/../../../web/uploads/images/';
        $imagePath = $imgRoot . $filename;

        $data = json_decode($request->getContent(), true);
        $this->compress(base64_decode($data['photo']), $imagePath, 30);

        $projectVoter->setHasPhoto(1);
        $projectVoter->setDidChanged(1);
        $projectVoter->setToSend(1);
        $projectVoter->setHasPhoto2023(1);
        $projectVoter->setHasId(0);
        $projectVoter->setPhotoAt(new \DateTime());
        $projectVoter->setUpdatedAt(new \DateTime());
        $projectVoter->setUpdatedBy("android_app");

        $em->flush();
        $em->clear();

        return new JsonResponse(null, 200);
    }

    public function compress($source, $destination, $quality)
    {
        $image = imagecreatefromstring($source);

        imagejpeg($image, $destination, $quality);

        return $destination;
    }

    /**
     * @Route("/ajax_get_m_project_voter_generate_id_no/{proId}/{proVoterId}",
     *     name="ajax_get_m_project_voter_generate_id_no",
     *    options={"expose" = true}
     * )
     * @Method("GET")
     */

    public function ajaxGenerateIdNoAction(Request $request, $proId, $proVoterId)
    {
        $em = $this->getDoctrine()->getManager();

        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy([
            'proId' => $proId,
            'proVoterId' => $proVoterId
        ]);

        $voterName = $proVoter->getVoterName();
        $munNo = $proVoter->getMunicipalityNo();

        if ($proVoter->getGeneratedIdNo() == '' || $proVoter->getGeneratedIdNo() == null) {
            $proIdCode = !empty($proVoter->getProIdCode()) ? $proVoter->getProIdCode() : $this->generateProIdCode($proId, $voterName, $munNo);
            $generatedIdNo = date('Y-m-d') . '-' . $proVoter->getMunicipalityNo() . '-' . $proVoter->getBrgyNo() . '-' . $proIdCode;

            $proVoter->setProIdCode($proIdCode);
            $proVoter->setGeneratedIdNo($generatedIdNo);
            $proVoter->setDateGenerated(date('Y-m-d'));
        }

        $proVoter->setDidChanged(1);
        $proVoter->setToSend(1);
        $proVoter->setUpdatedAt(new \DateTime());
        $proVoter->setUpdatedBy('android-app');
        $proVoter->setRemarks($request->get('remarks'));
        $proVoter->setStatus('A');

        $validator = $this->get('validator');
        $violations = $validator->validate($proVoter);

        $errors = [];

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse($errors, 400);
        }

        $em->flush();

        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($proVoter), 200);
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
     * @Route("/ajax_get_m_project_voter_reprint_id/{proId}/{voterId}",
     *     name="ajax_get_m_project_voter_reprint_id",
     *    options={"expose" = true}
     * )
     * @Method("GET")
     */

    public function ajaxResetIdAction(Request $request, $proId, $voterId)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:Voter")->find($voterId);

        if (!$entity) {
            return new JsonResponse(null, 404);
        }

        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy([
            'proId' => $proId,
            'voterId' => $voterId,
        ]);

        if (!$proVoter) {
            return new JsonResponse(null, 404);
        }

        $proVoter->setHasId(null);
        $proVoter->setHasPhoto(1);
        $proVoter->setDidChange(1);
        $proVoter->setUpdatedAt(new \DateTime());
        $proVoter->setUpdatedBy('android_app');

        $em->flush();
        $em->clear();

        return new JsonResponse(null, 200);
    }

    /**
     * @Route("/ajax_get_m_project_voter_reprint_id_alt/{proId}/{proVoterId}",
     *     name="ajax_get_m_project_voter_reprint_id_alt",
     *    options={"expose" = true}
     * )
     * @Method("GET")
     */

    public function ajaxResetIdAltAction(Request $request, $proId, $proVoterId)
    {
        $em = $this->getDoctrine()->getManager();

        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy([
            'proId' => $proId,
            'proVoterId' => $proVoterId,
        ]);

        if (!$proVoter) {
            return new JsonResponse(null, 404);
        }

        $proVoter->setHasId(null);
        $proVoter->setHasPhoto(1);
        $proVoter->setHasPhoto2023(1);
        $proVoter->setDidChanged(1);
        $proVoter->setUpdatedAt(new \DateTime());
        $proVoter->setUpdatedBy('android_app');

        $em->flush();
        $em->clear();

        return new JsonResponse(null, 200);
    }

    /**
     * @Route("/ajax_get_m_province_organization_summary",
     *       name="ajax_get_m_province_organization_summary",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetProvinceOrganizationSummary(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $provinceCode = empty($request->get("provinceCode")) ? 53 : $request->get("provinceCode");
        $electId = empty($request->get("electId")) ? null : $request->get("electId");
        $proId = empty($request->get("proId")) ? null : $request->get("proId");
        $createdAt = empty($request->get('createdAt')) ? null : $request->get('createdAt');

        if ($createdAt == null || $createdAt == 'null') {
            $createdAt = $this->getLastDateComputed($electId, $proId);
        }

        $sql = "SELECT m.*,
        (SELECT coalesce( SUM(s.total_voters),0) FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.province_code = ? AND s.elect_id = ? AND s.pro_id = ? ) as total_voters,
        (SELECT coalesce( count(DISTINCT s.brgy_no),0) FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.province_code = ? AND s.elect_id = ? AND s.pro_id = ? ) as total_barangays,
        (SELECT coalesce( count(s.sum_id),0) FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.province_code = ? AND s.elect_id = ? AND s.pro_id = ? ) as total_precincts,

        (SELECT coalesce( count(DISTINCT pv.clustered_precinct),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_clustered_precincts,
        (SELECT coalesce(sum(pv.total_member),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_recruits,
        (SELECT coalesce(sum(pv.total_level_1),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_ch,
        (SELECT coalesce(sum(pv.total_level_2),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl,
        (SELECT coalesce(sum(pv.total_level_3),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl0,
        (SELECT coalesce(sum(pv.total_level_4),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl1,
        (SELECT coalesce(sum(pv.total_level_5),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl2,
        (SELECT coalesce(sum(pv.total_level_6),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl3,
        (SELECT coalesce(sum(pv.total_level_7),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kjr,
        (SELECT coalesce(sum(pv.total_staff),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_staff,
        (SELECT coalesce(sum(pv.total_others),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_others,

        (SELECT coalesce(sum(pv.total_with_id_member),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_recruits,
        (SELECT coalesce(sum(pv.total_with_id_level_1),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_ch,
        (SELECT coalesce(sum(pv.total_with_id_level_2),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl,
        (SELECT coalesce(sum(pv.total_with_id_level_3),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl0,
        (SELECT coalesce(sum(pv.total_with_id_level_4),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl1,
        (SELECT coalesce(sum(pv.total_with_id_level_5),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl2,
        (SELECT coalesce(sum(pv.total_with_id_level_6),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl3,
        (SELECT coalesce(sum(pv.total_with_id_level_7),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kjr,
        (SELECT coalesce(sum(pv.total_with_id_staff),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_staff,
        (SELECT coalesce(sum(pv.total_with_id_others),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_others,

        (SELECT coalesce(sum(pv.total_submitted),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_submitted,
        (SELECT coalesce(sum(pv.total_has_submitted_level_1),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_ch,
        (SELECT coalesce(sum(pv.total_has_submitted_level_2),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl,
        (SELECT coalesce(sum(pv.total_has_submitted_level_3),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl0,
        (SELECT coalesce(sum(pv.total_has_submitted_level_4),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl1,
        (SELECT coalesce(sum(pv.total_has_submitted_level_5),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl2,
        (SELECT coalesce(sum(pv.total_has_submitted_level_6),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl3,
        (SELECT coalesce(sum(pv.total_has_submitted_level_7),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kjr,
        (SELECT coalesce(sum(pv.total_has_submitted_others),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_others,
        (SELECT coalesce(sum(pv.total_has_submitted_cellphone),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_cellphone,

        (SELECT coalesce(sum(pv.total_has_ast),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast,
        (SELECT coalesce(sum(pv.total_has_ast_level_1),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_ch,
        (SELECT coalesce(sum(pv.total_has_ast_level_2),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl,
        (SELECT coalesce(sum(pv.total_has_ast_level_3),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl0,
        (SELECT coalesce(sum(pv.total_has_ast_level_4),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl1,
        (SELECT coalesce(sum(pv.total_has_ast_level_5),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl2,
        (SELECT coalesce(sum(pv.total_has_ast_level_6),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl3,
        (SELECT coalesce(sum(pv.total_has_ast_level_7),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kjr,
        (SELECT coalesce(sum(pv.total_has_ast_others),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_others,
        (SELECT coalesce(sum(pv.total_has_ast_staff),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_staff,
        (SELECT coalesce(sum(pv.total_has_ast_cellphone),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_cellphone,

        (SELECT coalesce(sum(pv.total_has_cellphone),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_cellphone,
        (SELECT coalesce(sum(pv.total_with_id_cellphone),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_cellphone

        FROM  psw_municipality m
        WHERE m.province_code = ? AND m.municipality_no IN ('01','16') ";

        $sql .= " ORDER BY m.name ASC";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->bindValue(2, $electId);
        $stmt->bindValue(3, $proId);

        $stmt->bindValue(4, $provinceCode);
        $stmt->bindValue(5, $electId);
        $stmt->bindValue(6, $proId);

        $stmt->bindValue(7, $provinceCode);
        $stmt->bindValue(8, $electId);
        $stmt->bindValue(9, $proId);

        $stmt->bindValue(10, $provinceCode);
        $stmt->bindValue(11, $electId);
        $stmt->bindValue(12, $proId);
        $stmt->bindValue(13, $createdAt);

        $stmt->bindValue(14, $provinceCode);
        $stmt->bindValue(15, $electId);
        $stmt->bindValue(16, $proId);
        $stmt->bindValue(17, $createdAt);

        $stmt->bindValue(18, $provinceCode);
        $stmt->bindValue(19, $electId);
        $stmt->bindValue(20, $proId);
        $stmt->bindValue(21, $createdAt);

        $stmt->bindValue(22, $provinceCode);
        $stmt->bindValue(23, $electId);
        $stmt->bindValue(24, $proId);
        $stmt->bindValue(25, $createdAt);

        $stmt->bindValue(26, $provinceCode);
        $stmt->bindValue(27, $electId);
        $stmt->bindValue(28, $proId);
        $stmt->bindValue(29, $createdAt);

        $stmt->bindValue(30, $provinceCode);
        $stmt->bindValue(31, $electId);
        $stmt->bindValue(32, $proId);
        $stmt->bindValue(33, $createdAt);

        $stmt->bindValue(34, $provinceCode);
        $stmt->bindValue(35, $electId);
        $stmt->bindValue(36, $proId);
        $stmt->bindValue(37, $createdAt);

        $stmt->bindValue(38, $provinceCode);
        $stmt->bindValue(39, $electId);
        $stmt->bindValue(40, $proId);
        $stmt->bindValue(41, $createdAt);

        $stmt->bindValue(42, $provinceCode);
        $stmt->bindValue(43, $electId);
        $stmt->bindValue(44, $proId);
        $stmt->bindValue(45, $createdAt);

        $stmt->bindValue(46, $provinceCode);
        $stmt->bindValue(47, $electId);
        $stmt->bindValue(48, $proId);
        $stmt->bindValue(49, $createdAt);

        $stmt->bindValue(50, $provinceCode);
        $stmt->bindValue(51, $electId);
        $stmt->bindValue(52, $proId);
        $stmt->bindValue(53, $createdAt);

        $stmt->bindValue(54, $provinceCode);
        $stmt->bindValue(55, $electId);
        $stmt->bindValue(56, $proId);
        $stmt->bindValue(57, $createdAt);

        $stmt->bindValue(58, $provinceCode);
        $stmt->bindValue(59, $electId);
        $stmt->bindValue(60, $proId);
        $stmt->bindValue(61, $createdAt);

        $stmt->bindValue(62, $provinceCode);
        $stmt->bindValue(63, $electId);
        $stmt->bindValue(64, $proId);
        $stmt->bindValue(65, $createdAt);

        $stmt->bindValue(66, $provinceCode);
        $stmt->bindValue(67, $electId);
        $stmt->bindValue(68, $proId);
        $stmt->bindValue(69, $createdAt);

        $stmt->bindValue(70, $provinceCode);
        $stmt->bindValue(71, $electId);
        $stmt->bindValue(72, $proId);
        $stmt->bindValue(73, $createdAt);

        $stmt->bindValue(74, $provinceCode);
        $stmt->bindValue(75, $electId);
        $stmt->bindValue(76, $proId);
        $stmt->bindValue(77, $createdAt);

        $stmt->bindValue(78, $provinceCode);
        $stmt->bindValue(79, $electId);
        $stmt->bindValue(80, $proId);
        $stmt->bindValue(81, $createdAt);

        $stmt->bindValue(82, $provinceCode);
        $stmt->bindValue(83, $electId);
        $stmt->bindValue(84, $proId);
        $stmt->bindValue(85, $createdAt);

        $stmt->bindValue(86, $provinceCode);
        $stmt->bindValue(87, $electId);
        $stmt->bindValue(88, $proId);
        $stmt->bindValue(89, $createdAt);

        $stmt->bindValue(90, $provinceCode);
        $stmt->bindValue(91, $electId);
        $stmt->bindValue(92, $proId);
        $stmt->bindValue(93, $createdAt);

        $stmt->bindValue(94, $provinceCode);
        $stmt->bindValue(95, $electId);
        $stmt->bindValue(96, $proId);
        $stmt->bindValue(97, $createdAt);

        $stmt->bindValue(98, $provinceCode);
        $stmt->bindValue(99, $electId);
        $stmt->bindValue(100, $proId);
        $stmt->bindValue(101, $createdAt);

        $stmt->bindValue(102, $provinceCode);
        $stmt->bindValue(103, $electId);
        $stmt->bindValue(104, $proId);
        $stmt->bindValue(105, $createdAt);

        $stmt->bindValue(106, $provinceCode);
        $stmt->bindValue(107, $electId);
        $stmt->bindValue(108, $proId);
        $stmt->bindValue(109, $createdAt);

        $stmt->bindValue(110, $provinceCode);
        $stmt->bindValue(111, $electId);
        $stmt->bindValue(112, $proId);
        $stmt->bindValue(113, $createdAt);

        $stmt->bindValue(114, $provinceCode);
        $stmt->bindValue(115, $electId);
        $stmt->bindValue(116, $proId);
        $stmt->bindValue(117, $createdAt);

        $stmt->bindValue(118, $provinceCode);
        $stmt->bindValue(119, $electId);
        $stmt->bindValue(120, $proId);
        $stmt->bindValue(121, $createdAt);

        $stmt->bindValue(122, $provinceCode);
        $stmt->bindValue(123, $electId);
        $stmt->bindValue(124, $proId);
        $stmt->bindValue(125, $createdAt);

        $stmt->bindValue(126, $provinceCode);
        $stmt->bindValue(127, $electId);
        $stmt->bindValue(128, $proId);
        $stmt->bindValue(129, $createdAt);

        $stmt->bindValue(130, $provinceCode);
        $stmt->bindValue(131, $electId);
        $stmt->bindValue(132, $proId);
        $stmt->bindValue(133, $createdAt);

        $stmt->bindValue(134, $provinceCode);
        $stmt->bindValue(135, $electId);
        $stmt->bindValue(136, $proId);
        $stmt->bindValue(137, $createdAt);

        $stmt->bindValue(138, $provinceCode);
        $stmt->bindValue(139, $electId);
        $stmt->bindValue(140, $proId);
        $stmt->bindValue(141, $createdAt);

        $stmt->bindValue(142, $provinceCode);
        $stmt->bindValue(143, $electId);
        $stmt->bindValue(144, $proId);
        $stmt->bindValue(145, $createdAt);

        $stmt->bindValue(146, $provinceCode);
        $stmt->bindValue(147, $electId);
        $stmt->bindValue(148, $proId);
        $stmt->bindValue(149, $createdAt);

        $stmt->bindValue(150, $provinceCode);
        $stmt->bindValue(151, $electId);
        $stmt->bindValue(152, $proId);
        $stmt->bindValue(153, $createdAt);

        $stmt->bindValue(154, $provinceCode);
        $stmt->bindValue(155, $electId);
        $stmt->bindValue(156, $proId);
        $stmt->bindValue(157, $createdAt);

        $stmt->bindValue(158, $provinceCode);
        $stmt->bindValue(159, $electId);
        $stmt->bindValue(160, $proId);
        $stmt->bindValue(161, $createdAt);

        $stmt->bindValue(162, $provinceCode);
        $stmt->bindValue(163, $electId);
        $stmt->bindValue(164, $proId);
        $stmt->bindValue(165, $createdAt);

        $stmt->bindValue(166, $provinceCode);
        $stmt->bindValue(167, $electId);
        $stmt->bindValue(168, $proId);
        $stmt->bindValue(169, $createdAt);

        $stmt->bindValue(170, $provinceCode);
        $stmt->bindValue(171, $electId);
        $stmt->bindValue(172, $proId);
        $stmt->bindValue(173, $createdAt);

        $stmt->bindValue(174, $provinceCode);
        $stmt->bindValue(175, $electId);
        $stmt->bindValue(176, $proId);
        $stmt->bindValue(177, $createdAt);

        $stmt->bindValue(178, $provinceCode);
        $stmt->bindValue(179, $electId);
        $stmt->bindValue(180, $proId);
        $stmt->bindValue(181, $createdAt);

        $stmt->bindValue(182, $provinceCode);
        $stmt->bindValue(183, $electId);
        $stmt->bindValue(184, $proId);
        $stmt->bindValue(185, $createdAt);

        $stmt->bindValue(186, $provinceCode);
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {

            $temp = $row;

            $temp['total_no_id_recruits'] = $row['total_recruits'] - $row['total_with_id_recruits'];
            $temp['total_no_id_ch'] = $row['total_ch'] - $row['total_with_id_ch'];
            $temp['total_no_id_kcl'] = $row['total_kcl'] - $row['total_with_id_kcl'];
            $temp['total_no_id_kcl0'] = $row['total_kcl0'] - $row['total_with_id_kcl0'];
            $temp['total_no_id_kcl1'] = $row['total_kcl1'] - $row['total_with_id_kcl1'];
            $temp['total_no_id_kcl2'] = $row['total_kcl2'] - $row['total_with_id_kcl2'];
            $temp['total_no_id_kcl3'] = $row['total_kcl3'] - $row['total_with_id_kcl3'];
            $temp['total_no_id_kjr'] = $row['total_kjr'] - $row['total_with_id_kjr'];
            $temp['total_no_id_staff'] = $row['total_staff'] - $row['total_with_id_staff'];
            $temp['total_no_id_others'] = $row['total_others'] - $row['total_with_id_others'];
            $temp['total_no_id_cellphone'] = $row['total_has_cellphone'] - $row['total_with_id_cellphone'];

            $temp['total_not_submitted_recruits'] = $row['total_recruits'] - $row['total_submitted'];
            $temp['total_not_submitted_ch'] = $row['total_ch'] - $row['total_has_submitted_ch'];
            $temp['total_not_submitted_kcl'] = $row['total_kcl'] - $row['total_has_submitted_kcl'];
            $temp['total_not_submitted_kcl0'] = $row['total_kcl0'] - $row['total_has_submitted_kcl0'];
            $temp['total_not_submitted_kcl1'] = $row['total_kcl1'] - $row['total_has_submitted_kcl1'];
            $temp['total_not_submitted_kcl2'] = $row['total_kcl2'] - $row['total_has_submitted_kcl2'];
            $temp['total_not_submitted_kcl3'] = $row['total_kcl3'] - $row['total_has_submitted_kcl3'];
            $temp['total_not_submitted_kjr'] = $row['total_kjr'] - $row['total_has_submitted_kjr'];
            $temp['total_not_submitted_others'] = $row['total_others'] - $row['total_has_submitted_others'];
            $temp['total_not_submitted_cellphone'] = $row['total_submitted'] - $row['total_has_submitted_cellphone'];

            $temp['total_no_ast'] = $row['total_recruits'] - $row['total_has_ast'];
            $temp['total_no_ast_ch'] = $row['total_ch'] - $row['total_has_ast_ch'];
            $temp['total_no_ast_kcl'] = $row['total_kcl'] - $row['total_has_ast_kcl'];
            $temp['total_no_ast_kcl0'] = $row['total_kcl0'] - $row['total_has_ast_kcl0'];
            $temp['total_no_ast_kcl1'] = $row['total_kcl1'] - $row['total_has_ast_kcl1'];
            $temp['total_no_ast_kcl2'] = $row['total_kcl2'] - $row['total_has_ast_kcl2'];
            $temp['total_no_ast_kcl3'] = $row['total_kcl3'] - $row['total_has_ast_kcl3'];
            $temp['total_no_ast_kjr'] = $row['total_kjr'] - $row['total_has_ast_kjr'];
            $temp['total_no_ast_others'] = $row['total_others'] - $row['total_has_ast_others'];
            $temp['total_no_ast_staff'] = $row['total_staff'] - $row['total_has_ast_staff'];
            $temp['total_no_ast_cellphone'] = $row['total_has_ast'] - $row['total_has_ast_cellphone'];

            $temp['total_tl'] = $temp['total_ch'] + $temp['total_kcl'];
            $temp['total_sl'] = $temp['total_kcl0'] + $temp['total_kcl1'] + $temp['total_kcl2'];
            $temp['total_members'] = $temp['total_kcl3'] + $temp['total_kjr'];

            $temp['total_with_id_tl'] = $temp['total_with_id_ch'] + $temp['total_with_id_kcl'];
            $temp['total_with_id_sl'] = $temp['total_with_id_kcl0'] + $temp['total_with_id_kcl1'] + $temp['total_with_id_kcl2'];
            $temp['total_with_id_members'] = $temp['total_with_id_kcl3'] + $temp['total_with_id_kjr'];

            $temp['total_no_id_tl'] = $temp['total_no_id_ch'] + $temp['total_no_id_kcl'];
            $temp['total_no_id_sl'] = $temp['total_no_id_kcl0'] + $temp['total_no_id_kcl1'] + $temp['total_no_id_kcl2'];
            $temp['total_no_id_members'] = $temp['total_no_id_kcl3'] + $temp['total_no_id_kjr'];

            $data[] = $temp;
        }

        return new JsonResponse($data);
    }

    private function getLastDateComputed($electId, $proId)
    {
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT * FROM tbl_project_voter_summary WHERE elect_id = ? AND pro_id = ? ORDER BY created_at DESC LIMIT 1";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $electId);
        $stmt->bindValue(2, $proId);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row == null ? null : $row['created_at'];
    }

    /**
     * @Route("/ajax_get_m_municipality_organization_summary",
     *       name="ajax_get_m_municipality_organization_summary",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetMunicipalityDataSummary(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $electId = empty($request->get("electId")) ? null : $request->get("electId");
        $proId = empty($request->get("proId")) ? null : $request->get("proId");
        $provinceCode = empty($request->get("provinceCode")) ? 53 : $request->get('provinceCode');
        $municipalityNo = $request->get("municipalityNo");
        $createdAt = empty($request->get('createdAt')) ? null : $request->get('createdAt');

        if ($createdAt == null || $createdAt == 'null') {
            $createdAt = $this->getLastDateComputed($electId, $proId);
        }

        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT m.*,
        (SELECT COALESCE(SUM(s.total_voters),0) FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no  AND s.province_code = ? AND s.elect_id = ? AND s.pro_id = ? ) as total_voters,
        (SELECT COALESCE(COUNT(DISTINCT s.precinct_no),0) FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.province_code = ? AND s.elect_id = ?  AND s.pro_id = ? ) as total_precincts,

        (SELECT coalesce(COUNT(DISTINCT pv.clustered_precinct),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_clustered_precincts,
        (SELECT coalesce(sum(pv.total_member),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_recruits,
        (SELECT coalesce(sum(pv.total_level_1),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_ch,
        (SELECT coalesce(sum(pv.total_level_2),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl,
        (SELECT coalesce(sum(pv.total_level_3),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl0,
        (SELECT coalesce(sum(pv.total_level_4),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl1,
        (SELECT coalesce(sum(pv.total_level_5),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl2,
        (SELECT coalesce(sum(pv.total_level_6),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl3,
        (SELECT coalesce(sum(pv.total_level_7),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kjr,
        (SELECT coalesce(sum(pv.total_staff),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_staff,
        (SELECT coalesce(sum(pv.total_others),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_others,

        (SELECT coalesce(sum(pv.total_with_id_member),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_recruits,
        (SELECT coalesce(sum(pv.total_with_id_level_1),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_ch,
        (SELECT coalesce(sum(pv.total_with_id_level_2),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl,
        (SELECT coalesce(sum(pv.total_with_id_level_3),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl0,
        (SELECT coalesce(sum(pv.total_with_id_level_4),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl1,
        (SELECT coalesce(sum(pv.total_with_id_level_5),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl2,
        (SELECT coalesce(sum(pv.total_with_id_level_6),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl3,
        (SELECT coalesce(sum(pv.total_with_id_level_7),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kjr,
        (SELECT coalesce(sum(pv.total_with_id_staff),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_staff,
        (SELECT coalesce(sum(pv.total_with_id_others),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_others,

        (SELECT coalesce(sum(pv.total_submitted),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_submitted,
        (SELECT coalesce(sum(pv.total_has_submitted_level_1),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_ch,
        (SELECT coalesce(sum(pv.total_has_submitted_level_2),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl,
        (SELECT coalesce(sum(pv.total_has_submitted_level_3),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl0,
        (SELECT coalesce(sum(pv.total_has_submitted_level_4),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl1,
        (SELECT coalesce(sum(pv.total_has_submitted_level_5),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl2,
        (SELECT coalesce(sum(pv.total_has_submitted_level_6),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl3,
        (SELECT coalesce(sum(pv.total_has_submitted_level_7),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kjr,
        (SELECT coalesce(sum(pv.total_has_submitted_others),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_others,
        (SELECT coalesce(sum(pv.total_has_submitted_cellphone),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_cellphone,

        (SELECT coalesce(sum(pv.total_has_ast),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast,
        (SELECT coalesce(sum(pv.total_has_ast_level_1),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_ch,
        (SELECT coalesce(sum(pv.total_has_ast_level_2),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl,
        (SELECT coalesce(sum(pv.total_has_ast_level_3),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl0,
        (SELECT coalesce(sum(pv.total_has_ast_level_4),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl1,
        (SELECT coalesce(sum(pv.total_has_ast_level_5),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl2,
        (SELECT coalesce(sum(pv.total_has_ast_level_6),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl3,
        (SELECT coalesce(sum(pv.total_has_ast_level_7),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kjr,
        (SELECT coalesce(sum(pv.total_has_ast_others),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_others,
        (SELECT coalesce(sum(pv.total_has_ast_staff),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_staff,
        (SELECT coalesce(sum(pv.total_has_ast_cellphone),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_cellphone,

        (SELECT coalesce(sum(pv.total_has_cellphone),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_cellphone,
        (SELECT coalesce(sum(pv.total_with_id_cellphone),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_cellphone,
        (SELECT coalesce(count(b.brgy_no),0) FROM psw_barangay b WHERE b.municipality_code = ? ) as total_barangays
        FROM  psw_municipality m WHERE m.province_code = ? AND m.municipality_no = ? ";

        $sql .= " ORDER BY m.name ASC";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->bindValue(2, $electId);
        $stmt->bindValue(3, $proId);

        $stmt->bindValue(4, $provinceCode);
        $stmt->bindValue(5, $electId);
        $stmt->bindValue(6, $proId);

        $stmt->bindValue(7, $provinceCode);
        $stmt->bindValue(8, $electId);
        $stmt->bindValue(9, $proId);
        $stmt->bindValue(10, $createdAt);

        $stmt->bindValue(11, $provinceCode);
        $stmt->bindValue(12, $electId);
        $stmt->bindValue(13, $proId);
        $stmt->bindValue(14, $createdAt);

        $stmt->bindValue(15, $provinceCode);
        $stmt->bindValue(16, $electId);
        $stmt->bindValue(17, $proId);
        $stmt->bindValue(18, $createdAt);

        $stmt->bindValue(19, $provinceCode);
        $stmt->bindValue(20, $electId);
        $stmt->bindValue(21, $proId);
        $stmt->bindValue(22, $createdAt);

        $stmt->bindValue(23, $provinceCode);
        $stmt->bindValue(24, $electId);
        $stmt->bindValue(25, $proId);
        $stmt->bindValue(26, $createdAt);

        $stmt->bindValue(27, $provinceCode);
        $stmt->bindValue(28, $electId);
        $stmt->bindValue(29, $proId);
        $stmt->bindValue(30, $createdAt);

        $stmt->bindValue(31, $provinceCode);
        $stmt->bindValue(32, $electId);
        $stmt->bindValue(33, $proId);
        $stmt->bindValue(34, $createdAt);

        $stmt->bindValue(35, $provinceCode);
        $stmt->bindValue(36, $electId);
        $stmt->bindValue(37, $proId);
        $stmt->bindValue(38, $createdAt);

        $stmt->bindValue(39, $provinceCode);
        $stmt->bindValue(40, $electId);
        $stmt->bindValue(41, $proId);
        $stmt->bindValue(42, $createdAt);

        $stmt->bindValue(43, $provinceCode);
        $stmt->bindValue(44, $electId);
        $stmt->bindValue(45, $proId);
        $stmt->bindValue(46, $createdAt);

        $stmt->bindValue(47, $provinceCode);
        $stmt->bindValue(48, $electId);
        $stmt->bindValue(49, $proId);
        $stmt->bindValue(50, $createdAt);

        $stmt->bindValue(51, $provinceCode);
        $stmt->bindValue(52, $electId);
        $stmt->bindValue(53, $proId);
        $stmt->bindValue(54, $createdAt);

        $stmt->bindValue(55, $provinceCode);
        $stmt->bindValue(56, $electId);
        $stmt->bindValue(57, $proId);
        $stmt->bindValue(58, $createdAt);

        $stmt->bindValue(59, $provinceCode);
        $stmt->bindValue(60, $electId);
        $stmt->bindValue(61, $proId);
        $stmt->bindValue(62, $createdAt);

        $stmt->bindValue(63, $provinceCode);
        $stmt->bindValue(64, $electId);
        $stmt->bindValue(65, $proId);
        $stmt->bindValue(66, $createdAt);

        $stmt->bindValue(67, $provinceCode);
        $stmt->bindValue(68, $electId);
        $stmt->bindValue(69, $proId);
        $stmt->bindValue(70, $createdAt);

        $stmt->bindValue(71, $provinceCode);
        $stmt->bindValue(72, $electId);
        $stmt->bindValue(73, $proId);
        $stmt->bindValue(74, $createdAt);

        $stmt->bindValue(75, $provinceCode);
        $stmt->bindValue(76, $electId);
        $stmt->bindValue(77, $proId);
        $stmt->bindValue(78, $createdAt);

        $stmt->bindValue(79, $provinceCode);
        $stmt->bindValue(80, $electId);
        $stmt->bindValue(81, $proId);
        $stmt->bindValue(82, $createdAt);

        $stmt->bindValue(83, $provinceCode);
        $stmt->bindValue(84, $electId);
        $stmt->bindValue(85, $proId);
        $stmt->bindValue(86, $createdAt);

        $stmt->bindValue(87, $provinceCode);
        $stmt->bindValue(88, $electId);
        $stmt->bindValue(89, $proId);
        $stmt->bindValue(90, $createdAt);

        $stmt->bindValue(91, $provinceCode);
        $stmt->bindValue(92, $electId);
        $stmt->bindValue(93, $proId);
        $stmt->bindValue(94, $createdAt);

        $stmt->bindValue(95, $provinceCode);
        $stmt->bindValue(96, $electId);
        $stmt->bindValue(97, $proId);
        $stmt->bindValue(98, $createdAt);

        $stmt->bindValue(99, $provinceCode);
        $stmt->bindValue(100, $electId);
        $stmt->bindValue(101, $proId);
        $stmt->bindValue(102, $createdAt);

        $stmt->bindValue(103, $provinceCode);
        $stmt->bindValue(104, $electId);
        $stmt->bindValue(105, $proId);
        $stmt->bindValue(106, $createdAt);

        $stmt->bindValue(107, $provinceCode);
        $stmt->bindValue(108, $electId);
        $stmt->bindValue(109, $proId);
        $stmt->bindValue(110, $createdAt);

        $stmt->bindValue(111, $provinceCode);
        $stmt->bindValue(112, $electId);
        $stmt->bindValue(113, $proId);
        $stmt->bindValue(114, $createdAt);

        $stmt->bindValue(115, $provinceCode);
        $stmt->bindValue(116, $electId);
        $stmt->bindValue(117, $proId);
        $stmt->bindValue(118, $createdAt);

        $stmt->bindValue(119, $provinceCode);
        $stmt->bindValue(120, $electId);
        $stmt->bindValue(121, $proId);
        $stmt->bindValue(122, $createdAt);

        $stmt->bindValue(123, $provinceCode);
        $stmt->bindValue(124, $electId);
        $stmt->bindValue(125, $proId);
        $stmt->bindValue(126, $createdAt);

        $stmt->bindValue(127, $provinceCode);
        $stmt->bindValue(128, $electId);
        $stmt->bindValue(129, $proId);
        $stmt->bindValue(130, $createdAt);

        $stmt->bindValue(131, $provinceCode);
        $stmt->bindValue(132, $electId);
        $stmt->bindValue(133, $proId);
        $stmt->bindValue(134, $createdAt);

        $stmt->bindValue(135, $provinceCode);
        $stmt->bindValue(136, $electId);
        $stmt->bindValue(137, $proId);
        $stmt->bindValue(138, $createdAt);

        $stmt->bindValue(139, $provinceCode);
        $stmt->bindValue(140, $electId);
        $stmt->bindValue(141, $proId);
        $stmt->bindValue(142, $createdAt);

        $stmt->bindValue(143, $provinceCode);
        $stmt->bindValue(144, $electId);
        $stmt->bindValue(145, $proId);
        $stmt->bindValue(146, $createdAt);

        $stmt->bindValue(147, $provinceCode);
        $stmt->bindValue(148, $electId);
        $stmt->bindValue(149, $proId);
        $stmt->bindValue(150, $createdAt);

        $stmt->bindValue(151, $provinceCode);
        $stmt->bindValue(152, $electId);
        $stmt->bindValue(153, $proId);
        $stmt->bindValue(154, $createdAt);

        $stmt->bindValue(155, $provinceCode);
        $stmt->bindValue(156, $electId);
        $stmt->bindValue(157, $proId);
        $stmt->bindValue(158, $createdAt);

        $stmt->bindValue(159, $provinceCode);
        $stmt->bindValue(160, $electId);
        $stmt->bindValue(161, $proId);
        $stmt->bindValue(162, $createdAt);

        $stmt->bindValue(163, $provinceCode);
        $stmt->bindValue(164, $electId);
        $stmt->bindValue(165, $proId);
        $stmt->bindValue(166, $createdAt);

        $stmt->bindValue(167, $provinceCode);
        $stmt->bindValue(168, $electId);
        $stmt->bindValue(169, $proId);
        $stmt->bindValue(170, $createdAt);

        $stmt->bindValue(171, $provinceCode);
        $stmt->bindValue(172, $electId);
        $stmt->bindValue(173, $proId);
        $stmt->bindValue(174, $createdAt);

        $stmt->bindValue(175, $provinceCode);
        $stmt->bindValue(176, $electId);
        $stmt->bindValue(177, $proId);
        $stmt->bindValue(178, $createdAt);

        $stmt->bindValue(179, $provinceCode);
        $stmt->bindValue(180, $electId);
        $stmt->bindValue(181, $proId);
        $stmt->bindValue(182, $createdAt);

        $stmt->bindValue(183, $provinceCode . $municipalityNo);
        $stmt->bindValue(184, $provinceCode);
        $stmt->bindValue(185, $municipalityNo);

        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {

            $temp = $row;

            $temp['total_no_id_recruits'] = $row['total_recruits'] - $row['total_with_id_recruits'];
            $temp['total_no_id_ch'] = $row['total_ch'] - $row['total_with_id_ch'];
            $temp['total_no_id_kcl'] = $row['total_kcl'] - $row['total_with_id_kcl'];
            $temp['total_no_id_kcl0'] = $row['total_kcl0'] - $row['total_with_id_kcl0'];
            $temp['total_no_id_kcl1'] = $row['total_kcl1'] - $row['total_with_id_kcl1'];
            $temp['total_no_id_kcl2'] = $row['total_kcl2'] - $row['total_with_id_kcl2'];
            $temp['total_no_id_kcl3'] = $row['total_kcl3'] - $row['total_with_id_kcl3'];
            $temp['total_no_id_kjr'] = $row['total_kjr'] - $row['total_with_id_kjr'];
            $temp['total_no_id_staff'] = $row['total_staff'] - $row['total_with_id_staff'];
            $temp['total_no_id_others'] = $row['total_others'] - $row['total_with_id_others'];
            $temp['total_no_id_cellphone'] = $row['total_has_cellphone'] - $row['total_with_id_cellphone'];

            $temp['total_not_submitted_recruits'] = $row['total_recruits'] - $row['total_submitted'];
            $temp['total_not_submitted_ch'] = $row['total_ch'] - $row['total_has_submitted_ch'];
            $temp['total_not_submitted_kcl'] = $row['total_kcl'] - $row['total_has_submitted_kcl'];
            $temp['total_not_submitted_kcl0'] = $row['total_kcl0'] - $row['total_has_submitted_kcl0'];
            $temp['total_not_submitted_kcl1'] = $row['total_kcl1'] - $row['total_has_submitted_kcl1'];
            $temp['total_not_submitted_kcl2'] = $row['total_kcl2'] - $row['total_has_submitted_kcl2'];
            $temp['total_not_submitted_kcl3'] = $row['total_kcl3'] - $row['total_has_submitted_kcl3'];
            $temp['total_not_submitted_kjr'] = $row['total_kjr'] - $row['total_has_submitted_kjr'];
            $temp['total_not_submitted_others'] = $row['total_others'] - $row['total_has_submitted_others'];
            $temp['total_not_submitted_cellphone'] = $row['total_submitted'] - $row['total_has_submitted_cellphone'];

            $temp['total_no_ast'] = $row['total_recruits'] - $row['total_has_ast'];
            $temp['total_no_ast_ch'] = $row['total_ch'] - $row['total_has_ast_ch'];
            $temp['total_no_ast_kcl'] = $row['total_kcl'] - $row['total_has_ast_kcl'];
            $temp['total_no_ast_kcl0'] = $row['total_kcl0'] - $row['total_has_ast_kcl0'];
            $temp['total_no_ast_kcl1'] = $row['total_kcl1'] - $row['total_has_ast_kcl1'];
            $temp['total_no_ast_kcl2'] = $row['total_kcl2'] - $row['total_has_ast_kcl2'];
            $temp['total_no_ast_kcl3'] = $row['total_kcl3'] - $row['total_has_ast_kcl3'];
            $temp['total_no_ast_kjr'] = $row['total_kjr'] - $row['total_has_ast_kjr'];
            $temp['total_no_ast_others'] = $row['total_others'] - $row['total_has_ast_others'];
            $temp['total_no_ast_staff'] = $row['total_staff'] - $row['total_has_ast_staff'];
            $temp['total_no_ast_cellphone'] = $row['total_has_ast'] - $row['total_has_ast_cellphone'];

            $temp['total_tl'] = $temp['total_ch'] + $temp['total_kcl'];
            $temp['total_sl'] = $temp['total_kcl0'] + $temp['total_kcl1'] + $temp['total_kcl2'];
            $temp['total_members'] = $temp['total_kcl3'] + $temp['total_kjr'];

            $temp['total_with_id_tl'] = $temp['total_with_id_ch'] + $temp['total_with_id_kcl'];
            $temp['total_with_id_sl'] = $temp['total_with_id_kcl0'] + $temp['total_with_id_kcl1'] + $temp['total_with_id_kcl2'];
            $temp['total_with_id_members'] = $temp['total_with_id_kcl3'] + $temp['total_with_id_kjr'];

            $temp['total_no_id_tl'] = $temp['total_no_id_ch'] + $temp['total_no_id_kcl'];
            $temp['total_no_id_sl'] = $temp['total_no_id_kcl0'] + $temp['total_no_id_kcl1'] + $temp['total_no_id_kcl2'];
            $temp['total_no_id_members'] = $temp['total_no_id_kcl3'] + $temp['total_no_id_kjr'];

            $data = $temp;
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_get_m_barangay_organization_summary",
     *       name="ajax_get_m_barangay_organization_summary",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetBarangayDataSummary(Request $request)
    {
        $electId = $request->get("electId");
        $proId = $request->get("proId");
        $provinceCode = $request->get("provinceCode");
        $municipalityNo = $request->get("municipalityNo");
        $brgyNo = $request->get("brgyNo");
        $createdAt = empty($request->get('createdAt')) ? null : $request->get('createdAt');

        if ($createdAt == null || $createdAt == 'null') {
            $createdAt = $this->getLastDateComputed($electId, $proId);
        }

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT b.*,
        (SELECT COALESCE(SUM(s.total_voters),0) FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.brgy_no = b.brgy_no AND s.province_code = ? AND s.elect_id = ? AND s.pro_id = ? ) as total_voters,
        (SELECT COALESCE(COUNT(DISTINCT s.precinct_no),0) FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.brgy_no = b.brgy_no AND s.province_code = ? AND s.elect_id = ?  AND s.pro_id = ? ) as total_precincts,

        (SELECT coalesce(count(DISTINCT pv.clustered_precinct),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_clustered_precincts,
        (SELECT coalesce(sum(pv.total_member),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_recruits,
        (SELECT coalesce(sum(pv.total_level_1),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_ch,
        (SELECT coalesce(sum(pv.total_level_2),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl,
        (SELECT coalesce(sum(pv.total_level_3),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl0,
        (SELECT coalesce(sum(pv.total_level_4),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl1,
        (SELECT coalesce(sum(pv.total_level_5),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl2,
        (SELECT coalesce(sum(pv.total_level_6),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl3,
        (SELECT coalesce(sum(pv.total_level_7),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kjr,
        (SELECT coalesce(sum(pv.total_staff),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_staff,
        (SELECT coalesce(sum(pv.total_others),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_others,

        (SELECT coalesce(sum(pv.total_with_id_member),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_recruits,
        (SELECT coalesce(sum(pv.total_with_id_level_1),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_ch,
        (SELECT coalesce(sum(pv.total_with_id_level_2),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl,
        (SELECT coalesce(sum(pv.total_with_id_level_3),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl0,
        (SELECT coalesce(sum(pv.total_with_id_level_4),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl1,
        (SELECT coalesce(sum(pv.total_with_id_level_5),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl2,
        (SELECT coalesce(sum(pv.total_with_id_level_6),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl3,
        (SELECT coalesce(sum(pv.total_with_id_level_7),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kjr,
        (SELECT coalesce(sum(pv.total_with_id_staff),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_staff,
        (SELECT coalesce(sum(pv.total_with_id_others),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_others,

        (SELECT coalesce(sum(pv.total_submitted),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_submitted,
        (SELECT coalesce(sum(pv.total_has_submitted_level_1),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_ch,
        (SELECT coalesce(sum(pv.total_has_submitted_level_2),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl,
        (SELECT coalesce(sum(pv.total_has_submitted_level_3),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl0,
        (SELECT coalesce(sum(pv.total_has_submitted_level_4),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl1,
        (SELECT coalesce(sum(pv.total_has_submitted_level_5),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl2,
        (SELECT coalesce(sum(pv.total_has_submitted_level_6),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl3,
        (SELECT coalesce(sum(pv.total_has_submitted_level_7),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kjr,
        (SELECT coalesce(sum(pv.total_has_submitted_others),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_others,
        (SELECT coalesce(sum(pv.total_has_submitted_cellphone),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_cellphone,

        (SELECT coalesce(sum(pv.total_has_ast),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast,
        (SELECT coalesce(sum(pv.total_has_ast_level_1),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_ch,
        (SELECT coalesce(sum(pv.total_has_ast_level_2),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl,
        (SELECT coalesce(sum(pv.total_has_ast_level_3),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl0,
        (SELECT coalesce(sum(pv.total_has_ast_level_4),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl1,
        (SELECT coalesce(sum(pv.total_has_ast_level_5),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl2,
        (SELECT coalesce(sum(pv.total_has_ast_level_6),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl3,
        (SELECT coalesce(sum(pv.total_has_ast_level_7),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kjr,
        (SELECT coalesce(sum(pv.total_has_ast_others),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_others,
        (SELECT coalesce(sum(pv.total_has_ast_staff),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_staff,
        (SELECT coalesce(sum(pv.total_has_ast_cellphone),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_cellphone,

        (SELECT coalesce(sum(pv.total_has_cellphone),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_cellphone,
        (SELECT coalesce(sum(pv.total_with_id_cellphone),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_cellphone

        FROM  psw_barangay b INNER JOIN psw_municipality m ON m.municipality_code = b.municipality_code
        WHERE b.municipality_code = ? AND b.brgy_no = ? ";

        $sql .= " ORDER BY b.name ASC";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->bindValue(2, $electId);
        $stmt->bindValue(3, $proId);

        $stmt->bindValue(4, $provinceCode);
        $stmt->bindValue(5, $electId);
        $stmt->bindValue(6, $proId);

        $stmt->bindValue(7, $provinceCode);
        $stmt->bindValue(8, $electId);
        $stmt->bindValue(9, $proId);
        $stmt->bindValue(10, $createdAt);

        $stmt->bindValue(11, $provinceCode);
        $stmt->bindValue(12, $electId);
        $stmt->bindValue(13, $proId);
        $stmt->bindValue(14, $createdAt);

        $stmt->bindValue(15, $provinceCode);
        $stmt->bindValue(16, $electId);
        $stmt->bindValue(17, $proId);
        $stmt->bindValue(18, $createdAt);

        $stmt->bindValue(19, $provinceCode);
        $stmt->bindValue(20, $electId);
        $stmt->bindValue(21, $proId);
        $stmt->bindValue(22, $createdAt);

        $stmt->bindValue(23, $provinceCode);
        $stmt->bindValue(24, $electId);
        $stmt->bindValue(25, $proId);
        $stmt->bindValue(26, $createdAt);

        $stmt->bindValue(27, $provinceCode);
        $stmt->bindValue(28, $electId);
        $stmt->bindValue(29, $proId);
        $stmt->bindValue(30, $createdAt);

        $stmt->bindValue(31, $provinceCode);
        $stmt->bindValue(32, $electId);
        $stmt->bindValue(33, $proId);
        $stmt->bindValue(34, $createdAt);

        $stmt->bindValue(35, $provinceCode);
        $stmt->bindValue(36, $electId);
        $stmt->bindValue(37, $proId);
        $stmt->bindValue(38, $createdAt);

        $stmt->bindValue(39, $provinceCode);
        $stmt->bindValue(40, $electId);
        $stmt->bindValue(41, $proId);
        $stmt->bindValue(42, $createdAt);

        $stmt->bindValue(43, $provinceCode);
        $stmt->bindValue(44, $electId);
        $stmt->bindValue(45, $proId);
        $stmt->bindValue(46, $createdAt);

        $stmt->bindValue(47, $provinceCode);
        $stmt->bindValue(48, $electId);
        $stmt->bindValue(49, $proId);
        $stmt->bindValue(50, $createdAt);

        $stmt->bindValue(51, $provinceCode);
        $stmt->bindValue(52, $electId);
        $stmt->bindValue(53, $proId);
        $stmt->bindValue(54, $createdAt);

        $stmt->bindValue(55, $provinceCode);
        $stmt->bindValue(56, $electId);
        $stmt->bindValue(57, $proId);
        $stmt->bindValue(58, $createdAt);

        $stmt->bindValue(59, $provinceCode);
        $stmt->bindValue(60, $electId);
        $stmt->bindValue(61, $proId);
        $stmt->bindValue(62, $createdAt);

        $stmt->bindValue(63, $provinceCode);
        $stmt->bindValue(64, $electId);
        $stmt->bindValue(65, $proId);
        $stmt->bindValue(66, $createdAt);

        $stmt->bindValue(67, $provinceCode);
        $stmt->bindValue(68, $electId);
        $stmt->bindValue(69, $proId);
        $stmt->bindValue(70, $createdAt);

        $stmt->bindValue(71, $provinceCode);
        $stmt->bindValue(72, $electId);
        $stmt->bindValue(73, $proId);
        $stmt->bindValue(74, $createdAt);

        $stmt->bindValue(75, $provinceCode);
        $stmt->bindValue(76, $electId);
        $stmt->bindValue(77, $proId);
        $stmt->bindValue(78, $createdAt);

        $stmt->bindValue(79, $provinceCode);
        $stmt->bindValue(80, $electId);
        $stmt->bindValue(81, $proId);
        $stmt->bindValue(82, $createdAt);

        $stmt->bindValue(83, $provinceCode);
        $stmt->bindValue(84, $electId);
        $stmt->bindValue(85, $proId);
        $stmt->bindValue(86, $createdAt);

        $stmt->bindValue(87, $provinceCode);
        $stmt->bindValue(88, $electId);
        $stmt->bindValue(89, $proId);
        $stmt->bindValue(90, $createdAt);

        $stmt->bindValue(91, $provinceCode);
        $stmt->bindValue(92, $electId);
        $stmt->bindValue(93, $proId);
        $stmt->bindValue(94, $createdAt);

        $stmt->bindValue(95, $provinceCode);
        $stmt->bindValue(96, $electId);
        $stmt->bindValue(97, $proId);
        $stmt->bindValue(98, $createdAt);

        $stmt->bindValue(99, $provinceCode);
        $stmt->bindValue(100, $electId);
        $stmt->bindValue(101, $proId);
        $stmt->bindValue(102, $createdAt);

        $stmt->bindValue(103, $provinceCode);
        $stmt->bindValue(104, $electId);
        $stmt->bindValue(105, $proId);
        $stmt->bindValue(106, $createdAt);

        $stmt->bindValue(107, $provinceCode);
        $stmt->bindValue(108, $electId);
        $stmt->bindValue(109, $proId);
        $stmt->bindValue(110, $createdAt);

        $stmt->bindValue(111, $provinceCode);
        $stmt->bindValue(112, $electId);
        $stmt->bindValue(113, $proId);
        $stmt->bindValue(114, $createdAt);

        $stmt->bindValue(115, $provinceCode);
        $stmt->bindValue(116, $electId);
        $stmt->bindValue(117, $proId);
        $stmt->bindValue(118, $createdAt);

        $stmt->bindValue(119, $provinceCode);
        $stmt->bindValue(120, $electId);
        $stmt->bindValue(121, $proId);
        $stmt->bindValue(122, $createdAt);

        $stmt->bindValue(123, $provinceCode);
        $stmt->bindValue(124, $electId);
        $stmt->bindValue(125, $proId);
        $stmt->bindValue(126, $createdAt);

        $stmt->bindValue(127, $provinceCode);
        $stmt->bindValue(128, $electId);
        $stmt->bindValue(129, $proId);
        $stmt->bindValue(130, $createdAt);

        $stmt->bindValue(131, $provinceCode);
        $stmt->bindValue(132, $electId);
        $stmt->bindValue(133, $proId);
        $stmt->bindValue(134, $createdAt);

        $stmt->bindValue(135, $provinceCode);
        $stmt->bindValue(136, $electId);
        $stmt->bindValue(137, $proId);
        $stmt->bindValue(138, $createdAt);

        $stmt->bindValue(139, $provinceCode);
        $stmt->bindValue(140, $electId);
        $stmt->bindValue(141, $proId);
        $stmt->bindValue(142, $createdAt);

        $stmt->bindValue(143, $provinceCode);
        $stmt->bindValue(144, $electId);
        $stmt->bindValue(145, $proId);
        $stmt->bindValue(146, $createdAt);

        $stmt->bindValue(147, $provinceCode);
        $stmt->bindValue(148, $electId);
        $stmt->bindValue(149, $proId);
        $stmt->bindValue(150, $createdAt);

        $stmt->bindValue(151, $provinceCode);
        $stmt->bindValue(152, $electId);
        $stmt->bindValue(153, $proId);
        $stmt->bindValue(154, $createdAt);

        $stmt->bindValue(155, $provinceCode);
        $stmt->bindValue(156, $electId);
        $stmt->bindValue(157, $proId);
        $stmt->bindValue(158, $createdAt);

        $stmt->bindValue(159, $provinceCode);
        $stmt->bindValue(160, $electId);
        $stmt->bindValue(161, $proId);
        $stmt->bindValue(162, $createdAt);

        $stmt->bindValue(163, $provinceCode);
        $stmt->bindValue(164, $electId);
        $stmt->bindValue(165, $proId);
        $stmt->bindValue(166, $createdAt);

        $stmt->bindValue(167, $provinceCode);
        $stmt->bindValue(168, $electId);
        $stmt->bindValue(169, $proId);
        $stmt->bindValue(170, $createdAt);

        $stmt->bindValue(171, $provinceCode);
        $stmt->bindValue(172, $electId);
        $stmt->bindValue(173, $proId);
        $stmt->bindValue(174, $createdAt);

        $stmt->bindValue(175, $provinceCode);
        $stmt->bindValue(176, $electId);
        $stmt->bindValue(177, $proId);
        $stmt->bindValue(178, $createdAt);

        $stmt->bindValue(179, $provinceCode);
        $stmt->bindValue(180, $electId);
        $stmt->bindValue(181, $proId);
        $stmt->bindValue(182, $createdAt);

        $stmt->bindValue(183, $provinceCode . $municipalityNo);
        $stmt->bindValue(184, $brgyNo);
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {

            $temp = $row;

            $temp['total_no_id_recruits'] = $row['total_recruits'] - $row['total_with_id_recruits'];
            $temp['total_no_id_ch'] = $row['total_ch'] - $row['total_with_id_ch'];
            $temp['total_no_id_kcl'] = $row['total_kcl'] - $row['total_with_id_kcl'];
            $temp['total_no_id_kcl0'] = $row['total_kcl0'] - $row['total_with_id_kcl0'];
            $temp['total_no_id_kcl1'] = $row['total_kcl1'] - $row['total_with_id_kcl1'];
            $temp['total_no_id_kcl2'] = $row['total_kcl2'] - $row['total_with_id_kcl2'];
            $temp['total_no_id_kcl3'] = $row['total_kcl3'] - $row['total_with_id_kcl3'];
            $temp['total_no_id_kjr'] = $row['total_kjr'] - $row['total_with_id_kjr'];
            $temp['total_no_id_staff'] = $row['total_staff'] - $row['total_with_id_staff'];
            $temp['total_no_id_others'] = $row['total_others'] - $row['total_with_id_others'];
            $temp['total_no_id_cellphone'] = $row['total_has_cellphone'] - $row['total_with_id_cellphone'];

            $temp['total_not_submitted_recruits'] = $row['total_recruits'] - $row['total_submitted'];
            $temp['total_not_submitted_ch'] = $row['total_ch'] - $row['total_has_submitted_ch'];
            $temp['total_not_submitted_kcl'] = $row['total_kcl'] - $row['total_has_submitted_kcl'];
            $temp['total_not_submitted_kcl0'] = $row['total_kcl0'] - $row['total_has_submitted_kcl0'];
            $temp['total_not_submitted_kcl1'] = $row['total_kcl1'] - $row['total_has_submitted_kcl1'];
            $temp['total_not_submitted_kcl2'] = $row['total_kcl2'] - $row['total_has_submitted_kcl2'];
            $temp['total_not_submitted_kcl3'] = $row['total_kcl3'] - $row['total_has_submitted_kcl3'];
            $temp['total_not_submitted_kjr'] = $row['total_kjr'] - $row['total_has_submitted_kjr'];
            $temp['total_not_submitted_others'] = $row['total_others'] - $row['total_has_submitted_others'];
            $temp['total_not_submitted_cellphone'] = $row['total_submitted'] - $row['total_has_submitted_cellphone'];

            $temp['total_no_ast'] = $row['total_recruits'] - $row['total_has_ast'];
            $temp['total_no_ast_ch'] = $row['total_ch'] - $row['total_has_ast_ch'];
            $temp['total_no_ast_kcl'] = $row['total_kcl'] - $row['total_has_ast_kcl'];
            $temp['total_no_ast_kcl0'] = $row['total_kcl0'] - $row['total_has_ast_kcl0'];
            $temp['total_no_ast_kcl1'] = $row['total_kcl1'] - $row['total_has_ast_kcl1'];
            $temp['total_no_ast_kcl2'] = $row['total_kcl2'] - $row['total_has_ast_kcl2'];
            $temp['total_no_ast_kcl3'] = $row['total_kcl3'] - $row['total_has_ast_kcl3'];
            $temp['total_no_ast_kjr'] = $row['total_kjr'] - $row['total_has_ast_kjr'];
            $temp['total_no_ast_others'] = $row['total_others'] - $row['total_has_ast_others'];
            $temp['total_no_ast_staff'] = $row['total_staff'] - $row['total_has_ast_staff'];
            $temp['total_no_ast_cellphone'] = $row['total_has_ast'] - $row['total_has_ast_cellphone'];

            $temp['total_tl'] = $temp['total_ch'] + $temp['total_kcl'];
            $temp['total_sl'] = $temp['total_kcl0'] + $temp['total_kcl1'] + $temp['total_kcl2'];
            $temp['total_members'] = $temp['total_kcl3'] + $temp['total_kjr'];

            $temp['total_with_id_tl'] = $temp['total_with_id_ch'] + $temp['total_with_id_kcl'];
            $temp['total_with_id_sl'] = $temp['total_with_id_kcl0'] + $temp['total_with_id_kcl1'] + $temp['total_with_id_kcl2'];
            $temp['total_with_id_members'] = $temp['total_with_id_kcl3'] + $temp['total_with_id_kjr'];

            $temp['total_no_id_tl'] = $temp['total_no_id_ch'] + $temp['total_no_id_kcl'];
            $temp['total_no_id_sl'] = $temp['total_no_id_kcl0'] + $temp['total_no_id_kcl1'] + $temp['total_no_id_kcl2'];
            $temp['total_no_id_members'] = $temp['total_no_id_kcl3'] + $temp['total_no_id_kjr'];

            $data = $temp;
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_m_get_kfc_voters",
     *       name="ajax_m_get_kfc_voters",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetVoters(Request $request)
    {
        $provinceCode = 53;
        $municipalityName = $request->get("municipalityName");
        $barangayName = $request->get("barangayName");
        $precinctNo = $request->get("precinctNo");
        $voterNo = $request->get('voterNo');

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT * FROM tbl_project_voter
                WHERE pro_id = 2
                AND elect_id = 3
                AND province_code = ?
                AND municipality_name = ?
                AND barangay_name = ?
                AND precinct_no = ?
                AND voter_no = ?
                ORDER BY voter_no  ASC , voter_name ASC
                LIMIT 1 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->bindValue(2, $municipalityName);
        $stmt->bindValue(3, $barangayName);
        $stmt->bindValue(4, $precinctNo);
        $stmt->bindValue(5, $voterNo);

        $stmt->execute();
        $voter = $stmt->fetch();

        if ($voter == null) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($voter);
    }

    /**
     * @Route("/ajax_m_get_kfc_voterslist",
     *       name="ajax_m_get_kfc_voterslist",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetVoterslist(Request $request)
    {
        $municipalityName = empty($request->get("municipalityName")) ? null : $request->get("municipalityName");
        $barangayName = empty($request->get("barangayName")) ? null : $request->get("barangayName");
        $voterName = empty($request->get('voterName')) ? null : $request->get('voterName');

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT * FROM tbl_project_voter
                WHERE elect_id = 3
                AND pro_id = 2
                AND (municipality_name = ? OR ? is null )
                AND (voter_name like ? OR ? is null)
                ORDER BY voter_name ASC
                LIMIT 5 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipalityName);
        $stmt->bindValue(2, $municipalityName);
        $stmt->bindValue(3, '%' . $voterName . '%');
        $stmt->bindValue(4, $voterName);
        $stmt->execute();
        $voters = $stmt->fetchAll();

        if ($voters == null) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($voters);
    }

    /**
     * @Route("/ajax_m_patch_kfc_voter/{proVoterId}",
     *     name="ajax_m_patch_kfc_voter",
     *    options={"expose" = true}
     * )
     * @Method("PATCH")
     */

    public function ajaxPatchKfcVoterAction($proVoterId, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy([
            'proVoterId' => $proVoterId,
        ]);

        if (!$proVoter) {
            return null;
        }

        $data = json_decode($request->getContent(), true);
        $request->request->replace($data);

        $proVoter->setIsMember($request->get('isMember'));
        $proVoter->setIsBigkis($request->get('isBigkis'));
        $proVoter->setIsPulahan($request->get('isPulahan'));

        $proVoter->setIsExpired($request->get('isExpired'));

        $proVoter->setIsTransient($request->get('isTransient'));

        $proVoter->setIsBisaya($request->get('isBisaya'));
        $proVoter->setIsCuyonon($request->get('isCuyonon'));
        $proVoter->setIsTagalog($request->get('isTagalog'));
        $proVoter->setOthersSpecify(strtoupper($request->get('othersSpecify')));

        $proVoter->setNewBirthdate($request->get('birthdate'));
        $proVoter->setReligion(strtoupper($request->get('religion')));
        $proVoter->setCellphone($request->get('cellphone'));

        $proVoter->setUpdatedAt(new \DateTime());
        $proVoter->setUpdatedBy('android_app');
        $proVoter->setStatus('A');

        $validator = $this->get('validator');
        $violations = $validator->validate($proVoter);

        $errors = [];

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse($errors, 400);
        }

        $em->flush();

        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($proVoter));
    }

    /**
     * @Route("/ajax_m_post_pending_voter",
     *       name="ajax_m_post_pending_voter",
     *        options={ "expose" = true }
     * )
     * @Method("POST")
     */

    public function ajaxPostPendingVoter(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $data = json_decode($request->getContent(), true);
        $request->request->replace($data);

        $proId = $request->get('proId');
        $electId = $request->get('electId');
        $firstname = $request->get('firstname');
        $middlename = $request->get('middlename');
        $lastname = $request->get('lastname');
        $municipalityName = $request->get('municipalityName');
        $barangayName = $request->get('barangayName');
        $address = $request->get('address');
        $precinctNo = $request->get('precinctNo');
        $voterGroup = $request->get('voterGroup');
        $cellphone = $request->get('cellphone');

        $entity = new PendingVoter();
        $entity->setProId($proId);
        $entity->setElectId($electId);
        $entity->setFirstname(strtoupper($firstname));
        $entity->setMiddlename(strtoupper($middlename));
        $entity->setLastname(strtoupper($lastname));
        $entity->setMunicipalityName($municipalityName);
        $entity->setBarangayName($barangayName);
        $entity->setAddress($address);
        $entity->setPrecinctNo($precinctNo);
        $entity->setVoterGroup($voterGroup);
        $entity->setCellphone($cellphone);
        $entity->setCreatedAt(new \DateTime());
        $entity->setCreatedBy('android_app');
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

        $em->persist($entity);
        $em->flush();

        return new JsonResponse(null);
    }

    /**
     * @Route("/ajax_m_get_pending_voterslist",
     *       name="ajax_m_get_pending_voterslist",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetPendingVoterslist(Request $request)
    {
        $municipalityName = empty($request->get("municipalityName")) ? null : $request->get("municipalityName");
        $barangayName = empty($request->get("barangayName")) ? null : $request->get("barangayName");
        $voterName = empty($request->get('voterName')) ? null : $request->get('voterName');

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT * FROM tbl_pending_voter
                WHERE pro_id = 3
                AND elect_id = 3
                AND (municipality_name = ? OR ? is null )
                AND (barangay_name = ? OR ? is null )
                AND (
                    (firstname like ? OR  middlename like ? OR lastname like ? )
                    OR (? IS NULL AND ? IS NULL  AND ? IS NULL)
                )
                ORDER BY municipality_name ASC,firstname ASC,middlename ASC ,lastname ASC
                LIMIT 10 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipalityName);
        $stmt->bindValue(2, $municipalityName);
        $stmt->bindValue(3, $barangayName);
        $stmt->bindValue(4, $barangayName);
        $stmt->bindValue(5, '%' . $voterName . '%');
        $stmt->bindValue(6, '%' . $voterName . '%');
        $stmt->bindValue(7, '%' . $voterName . '%');
        $stmt->bindValue(8, $voterName);
        $stmt->bindValue(9, $voterName);
        $stmt->bindValue(10, $voterName);

        $stmt->execute();
        $voters = $stmt->fetchAll();

        if ($voters == null) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($voters);
    }

    /**
     * @Route("/ajax_m_get_barangays_by_name/{municipalityName}",
     *       name="ajax_m_get_barangays",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetBarangaysByName(Request $request, $municipalityName)
    {

        // $em = $this->getDoctrine()->getManager();

        // $sql = "SELECT * FROM psw_barangay b
        //         WHERE b.municipality_code = ? ORDER BY b.name ASC";

        // $stmt = $em->getConnection()->prepare($sql);
        // $stmt->bindValue(1,$municipalityCode);

        // $stmt->execute();
        // $barangays = $stmt->fetchAll();

        // if(count($barangays) <= 0)
        //     return new JsonResponse(array());

        // $em->clear();

        // return new JsonResponse($barangays);

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT DISTINCT barangay_name FROM tbl_project_voter pv
                WHERE pv.municipality_name = ? ORDER BY pv.barangay_name ASC";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipalityName);

        $stmt->execute();
        $barangays = $stmt->fetchAll();

        if (count($barangays) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($barangays);
    }

    /**
     * @Route("/ajax_m_get_precincts/{municipalityName}/{barangayName}",
     *       name="ajax_m_get_precincts",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetPrecincts(Request $request, $municipalityName, $barangayName)
    {

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT DISTINCT precinct_no FROM tbl_project_voter pv
                WHERE pv.municipality_name = ? AND pv.barangay_name = ? ORDER BY pv.precinct_no ASC";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipalityName);
        $stmt->bindValue(2, $barangayName);

        $stmt->execute();
        $barangays = $stmt->fetchAll();

        if (count($barangays) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($barangays);
    }

    /**
     * @Route("/ajax_get_fa_transactions", name="ajax_get_fa_transactions", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxGetDatatableFinancialAssistanceAction(Request $request)
    {
        $columns = array(
            0 => "h.trn_id",
            1 => "h.trn_no",
            2 => "h.trn_date",
            3 => "h.applicant_name",
            4 => "h.beneficiary_name",
            5 => "h.endorsed_by",
            6 => "h.municipality_no",
            7 => "h.barangay_no"
        );

        $sWhere = "";

        $select['h.trn_no'] = $request->get('trnNo');
        $select['h.trn_date'] = $request->get('trnDate');
        $select['h.applicant_name'] = $request->get('applicantName');
        $select['h.beneficiary_name'] = $request->get('beneficiaryName');
        $select['h.endorsed_by'] = $request->get('endorsedBy');
        $select['h.municipality_no'] = $request->get('municipalityNo');
        $select['h.barangay_no'] = $request->get('barangayNo');
        $select['m.name'] = $request->get('municipalityName');
        $select['b.name'] = $request->get('barangayName');
        $select['h.fiscal_year'] = $request->get('fiscalYear');

        foreach ($select as $key => $value) {
            $searchValue = $select[$key];
            if ($searchValue != null || !empty($searchValue)) {
                if ($key == "h.fiscal_year") {
                    $sWhere .= " AND " . $key . "=" . $searchValue;
                } else {
                    $sWhere .= " AND (" . $key . " LIKE '%" . $searchValue . "%' OR " . (empty($searchValue) ? null : "'" . $searchValue . "'") . " IS NULL ) ";
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

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(h.trn_id),0) FROM tbl_fa_hdr h 
                INNER JOIN psw_municipality m ON m.province_code = 53 AND m.municipality_no = h.municipality_no
                INNER JOIN psw_barangay b ON b.municipality_code = m.municipality_code AND b.brgy_no = h.barangay_no 
                WHERE 1 ";

        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.trn_id),0) FROM tbl_fa_hdr h 
                INNER JOIN psw_municipality m ON m.province_code = 53 AND m.municipality_no = h.municipality_no
                INNER JOIN psw_barangay b ON b.municipality_code = m.municipality_code AND b.brgy_no = h.barangay_no 
                WHERE 1 ";

        $sql .= $sWhere . ' ' . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT h.*, m.name AS municipality_name , b.name AS barangay_name
            FROM tbl_fa_hdr h 
            INNER JOIN psw_municipality m ON m.province_code = 53 AND m.municipality_no = h.municipality_no
            INNER JOIN psw_barangay b ON b.municipality_code = m.municipality_code AND b.brgy_no = h.barangay_no 
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
     * @Route("/ajax_get_fa_applicants", name="ajax_get_fa_applicants", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxGetDatatableFaApplicantsAction(Request $request)
    {
        $columns = array(
            0 => "h.trn_id",
            1 => "h.trn_no",
            2 => "h.trn_date",
            3 => "h.applicant_name",
            4 => "h.beneficiary_name",
            5 => "h.endorsed_by",
            6 => "h.municipality_no",
            7 => "h.barangay_no"
        );

        $sWhere = "";

        $select['d.trn_no'] = $request->get('trnNo');
        $select['d.trn_date'] = $request->get('trnDate');
        $select['d.applicant_name'] = $request->get('applicantName');
        $select['d.beneficiary_name'] = $request->get('beneficiaryName');
        $select['d.endorsed_by'] = $request->get('endorsedBy');
        $select['d.municipality_no'] = $request->get('municipalityNo');
        $select['d.barangay_no'] = $request->get('barangayNo');
        $select['m.name'] = $request->get('municipalityName');
        $select['b.name'] = $request->get('barangayName');
        $select['d.fiscal_year'] = $request->get('fiscalYear');
        $minTrn = empty($request->get('minTrn')) ? 0 : $request->get('minTrn');

        foreach ($select as $key => $value) {
            $searchValue = $select[$key];
            if ($searchValue != null || !empty($searchValue)) {
                if ($key == "d.fiscal_year") {
                    $sWhere .= " AND " . $key . "=" . $searchValue;
                } else {
                    $sWhere .= " AND (" . $key . " LIKE '%" . $searchValue . "%' OR " . (empty($searchValue) ? null : "'" . $searchValue . "'") . " IS NULL ) ";
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

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(DISTINCT d.applicant_name),0) FROM tbl_fa_daily_closing_dtl d 
                INNER JOIN psw_municipality m ON m.province_code = 53 AND m.municipality_no = d.municipality_no
                INNER JOIN psw_barangay b ON b.municipality_code = m.municipality_code AND b.brgy_no = d.barangay_no 
                WHERE 1 ";

        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(DISTINCT d.applicant_name),0) FROM tbl_fa_daily_closing_dtl d 
                INNER JOIN psw_municipality m ON m.province_code = 53 AND m.municipality_no = d.municipality_no
                INNER JOIN psw_barangay b ON b.municipality_code = m.municipality_code AND b.brgy_no = d.barangay_no  
                WHERE 1 ";

        $sql .= $sWhere . ' ' . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        if ($minTrn > 1) {
            $sql = "SELECT d.*, m.name AS municipality_name , b.name AS barangay_name,
            COUNT(*) total_transactions, SUM(granted_amt) AS total_amount_granted, COUNT(DISTINCT d.beneficiary_name) AS total_beneficiary
            FROM tbl_fa_daily_closing_dtl d 
            INNER JOIN psw_municipality m ON m.province_code = 53 AND m.municipality_no = d.municipality_no
            INNER JOIN psw_barangay b ON b.municipality_code = m.municipality_code AND b.brgy_no = d.barangay_no  
            WHERE 1 " . $sWhere . ' ' . $sOrder . " GROUP BY d.applicant_name HAVING total_transactions >= 2 LIMIT {$length} OFFSET {$start} ";

        } else {
            $sql = "SELECT d.*, m.name AS municipality_name , b.name AS barangay_name,
            COUNT(*) total_transactions, SUM(granted_amt) AS total_amount_granted, COUNT(DISTINCT d.beneficiary_name) AS total_beneficiary
            FROM tbl_fa_daily_closing_dtl d 
            INNER JOIN psw_municipality m ON m.province_code = 53 AND m.municipality_no = d.municipality_no
            INNER JOIN psw_barangay b ON b.municipality_code = m.municipality_code AND b.brgy_no = d.barangay_no  
            WHERE 1 " . $sWhere . ' ' . $sOrder . " GROUP BY d.applicant_name LIMIT {$length} OFFSET {$start} ";

        }

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
     * @Route("/ajax_get_fa_beneficiaries", name="ajax_get_fa_beneficiaries", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxGetDatatableFaBeneficiariesAction(Request $request)
    {
        $columns = array(
            0 => "h.trn_id",
            1 => "h.trn_no",
            2 => "h.trn_date",
            3 => "h.applicant_name",
            4 => "h.beneficiary_name",
            5 => "h.endorsed_by",
            6 => "h.municipality_no",
            7 => "h.barangay_no"
        );

        $sWhere = "";

        $select['d.trn_no'] = $request->get('trnNo');
        $select['d.trn_date'] = $request->get('trnDate');
        $select['d.applicant_name'] = $request->get('applicantName');
        $select['d.beneficiary_name'] = $request->get('beneficiaryName');
        $select['d.endorsed_by'] = $request->get('endorsedBy');
        $select['d.municipality_no'] = $request->get('municipalityNo');
        $select['d.barangay_no'] = $request->get('barangayNo');
        $select['m.name'] = $request->get('municipalityName');
        $select['b.name'] = $request->get('barangayName');
        $select['d.fiscal_year'] = $request->get('fiscalYear');
        $minTrn = empty($request->get('minTrn')) ? 0 : $request->get('minTrn');

        foreach ($select as $key => $value) {
            $searchValue = $select[$key];
            if ($searchValue != null || !empty($searchValue)) {
                if ($key == "d.fiscal_year") {
                    $sWhere .= " AND " . $key . "=" . $searchValue;
                } else {
                    $sWhere .= " AND (" . $key . " LIKE '%" . $searchValue . "%' OR " . (empty($searchValue) ? null : "'" . $searchValue . "'") . " IS NULL ) ";
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

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(DISTINCT d.beneficiary_name),0) FROM tbl_fa_daily_closing_dtl d 
                INNER JOIN psw_municipality m ON m.province_code = 53 AND m.municipality_no = d.municipality_no
                INNER JOIN psw_barangay b ON b.municipality_code = m.municipality_code AND b.brgy_no = d.barangay_no 
                WHERE 1 ";

        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(DISTINCT d.beneficiary_name),0) FROM tbl_fa_daily_closing_dtl d 
                INNER JOIN psw_municipality m ON m.province_code = 53 AND m.municipality_no = d.municipality_no
                INNER JOIN psw_barangay b ON b.municipality_code = m.municipality_code AND b.brgy_no = d.barangay_no  
                WHERE 1 ";

        $sql .= $sWhere . ' ' . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        if ($minTrn > 1) {
            $sql = "SELECT d.*, m.name AS municipality_name , b.name AS barangay_name,
            COUNT(*) total_transactions, SUM(granted_amt) AS total_amount_granted, COUNT(DISTINCT d.beneficiary_name) AS total_beneficiary
            FROM tbl_fa_daily_closing_dtl d 
            INNER JOIN psw_municipality m ON m.province_code = 53 AND m.municipality_no = d.municipality_no
            INNER JOIN psw_barangay b ON b.municipality_code = m.municipality_code AND b.brgy_no = d.barangay_no  
            WHERE 1 " . $sWhere . ' ' . $sOrder . " GROUP BY d.beneficiary_name HAVING total_transactions >= 2 LIMIT {$length} OFFSET {$start} ";

        } else {
            $sql = "SELECT d.*, m.name AS municipality_name , b.name AS barangay_name,
            COUNT(*) total_transactions, SUM(granted_amt) AS total_amount_granted, COUNT(DISTINCT d.beneficiary_name) AS total_beneficiary
            FROM tbl_fa_daily_closing_dtl d 
            INNER JOIN psw_municipality m ON m.province_code = 53 AND m.municipality_no = d.municipality_no
            INNER JOIN psw_barangay b ON b.municipality_code = m.municipality_code AND b.brgy_no = d.barangay_no  
            WHERE 1 " . $sWhere . ' ' . $sOrder . " GROUP BY d.beneficiary_name LIMIT {$length} OFFSET {$start} ";

        }

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
     * @Route("/ajax_upload_fa_photo",
     *     name="ajax_upload_fa_photo",
     *     options={"expose" = true}
     *     )
     * @Method("POST")
     */

    public function ajaxUploadFaPhoto(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $filename = 'testing_picture.jpg';
        $imgRoot = __DIR__ . '/../../../web/uploads/images/';
        $imagePath = $imgRoot . $filename;

        $data = json_decode($request->getContent(), true);
        $photo = $data['photo'];

        $base64 = base64_decode($photo, true);
        $image = imagecreatefromstring($base64);
        imagejpeg($image, $imagePath, 30);

        //  $image = imagecreatefromstring($source);

        // imagejpeg($image, $destination, $quality);

        // return $destination;
        //$this->compress(base64_decode($data['photo']), $imagePath, 30);


        return new JsonResponse(["base_64_str" => $photo], 200);
    }


    /**
     * @Route("/ajax_upload_fa_applicant_photo/{trnNo}",
     *     name="ajax_upload_fa_applicant_photo",
     *     options={"expose" = true}
     *     )
     * @Method("POST")
     */

    public function ajaxUploadFaApplicantPhoto(Request $request, $trnNo)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository("AppBundle:FinancialAssistanceHeader")->findOneBy(["trnNo" => $trnNo]);

        if (!$entity)
            return new JsonResponse(null, 404);

        $filename = $trnNo . '.jpg';
        $imgRoot = __DIR__ . '/../../../web/uploads/images/applicant/';
        $imagePath = $imgRoot . $filename;

        $data = json_decode($request->getContent(), true);
        $photo = $data['photo'];

        $base64 = base64_decode($photo, true);
        $image = imagecreatefromstring($base64);
        imagejpeg($image, $imagePath, 30);

        //  $image = imagecreatefromstring($source);

        // imagejpeg($image, $destination, $quality);

        // return $destination;
        //$this->compress(base64_decode($data['photo']), $imagePath, 30);


        return new JsonResponse(["base_64_str" => $photo], 200);
    }

    /**
     * @Route("/ajax_upload_fa_receiver_photo/{trnNo}",
     *     name="ajax_upload_fa_receiver_photo",
     *     options={"expose" = true}
     *     )
     * @Method("POST")
     */

    public function ajaxUploadFaReceiverPhoto(Request $request, $trnNo)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository("AppBundle:FinancialAssistanceHeader")->findOneBy(["trnNo" => $trnNo]);

        if (!$entity)
            return new JsonResponse(null, 404);

        $filename = $trnNo . '.jpg';
        $imgRoot = __DIR__ . '/../../../web/uploads/images/receiver/';
        $imagePath = $imgRoot . $filename;

        $data = json_decode($request->getContent(), true);
        $photo = $data['photo'];

        $base64 = base64_decode($photo, true);
        $image = imagecreatefromstring($base64);
        imagejpeg($image, $imagePath, 30);

        return new JsonResponse(["base_64_str" => $photo], 200);
    }

    /**
     * @Route("/photo/applicant/{filename}",
     *   name="ajax_get_fa_applicant_photo",
     *   options={"expose" = true}
     * )
     * @Method("GET")
     */

    public function ajaxGetFaApplicantPhotoAction($filename)
    {

        $rootDir = __DIR__ . '/../../../web/uploads/images/applicant/';
        $imagePath = $rootDir . $filename . '.jpg';

        if (!file_exists($imagePath)) {
            $imagePath = $rootDir . 'default.jpg';
        }

        $response = new BinaryFileResponse($imagePath);
        $response->headers->set('Content-Type', 'image/jpeg');

        return $response;
    }

    /**
     * @Route("/photo/receiver/{filename}",
     *   name="ajax_get_fa_receiver_photo",
     *   options={"expose" = true}
     * )
     * @Method("GET")
     */

    public function ajaxGetFaReceiverPhotoAction($filename)
    {

        $rootDir = __DIR__ . '/../../../web/uploads/images/receiver/';
        $imagePath = $rootDir . $filename . '.jpg';

        if (!file_exists($imagePath)) {
            $imagePath = $rootDir . 'default.jpg';
        }

        $response = new BinaryFileResponse($imagePath);
        $response->headers->set('Content-Type', 'image/jpeg');

        return $response;
    }

    /**
     * @Route("/ajax_m_get_project_voters_2023",
     *       name="ajax_m_get_project_voters_2023",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetJpmProjectVoters2023(Request $request)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");

        $provinceCode = $request->get('provinceCode');
        $municipalityNo = $request->get('municipalityNo');
        $municipalityName = $request->get('municipalityName');
        $barangayName = $request->get('barangayName');
        $voterGroup = $request->get('voterGroup');
        //$groupFilter = strtoupper($request->get("groupFilter"));

        $brgyNo = $request->get("brgyNo");
        $voterName = $request->get("voterName");
        $imgUrl = $this->getParameter('img_url');
        $batchSize = 10;
        $batchNo = $request->get("batchNo");

        $batchOffset = $batchNo * $batchSize;

        $sql = "SELECT pv.* FROM tbl_project_voter pv WHERE 1 AND ";

        if (!is_numeric($voterName)) {
            $sql .= " (pv.voter_name LIKE ? OR ? IS NULL ) ";
        } else {
            $sql .= " (pv.generated_id_no LIKE ? OR ? IS NULL ) ";
        }



        $sql .= "AND pv.elect_id = ? 
        AND (pv.municipality_name LIKE ? OR ? IS NULL) 
        AND (pv.barangay_name LIKE ? OR ? IS NULL) 
        AND pv.precinct_no IS NOT NULL 
        ORDER BY pv.voter_name ASC LIMIT {$batchSize} OFFSET {$batchOffset}";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, '%' . $voterName . '%');
        $stmt->bindValue(2, empty($voterName) ? null : '%' . $voterName . '%');
        $stmt->bindValue(3, 423);
        $stmt->bindValue(4, '%' . $municipalityName . '%');
        $stmt->bindValue(5, empty($municipalityName) ? null : '%' . $municipalityName . '%');
        $stmt->bindValue(6, '%' . $barangayName . '%');
        $stmt->bindValue(7, empty($barangayName) ? null : '%' . $barangayName . '%');
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['imgUrl'] = $imgUrl . '3_' . $row['generated_id_no'] . '?' . strtotime((new \DateTime())->format('Y-m-d H:i:s'));
            $row['cellphone_no'] = $row['cellphone'];
            $data[] = $row;
        }


        return new JsonResponse($data);
    }

    /**
     * @Route("/election/ajax_get_election_municipality_result", 
     *       name="ajax_get_election_municipality_result",
     *		options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetElectionMunicipalityResult(Request $request)
    {

        $em = $this->getDoctrine()->getManager("election");

        $provinceCode = 53; //$request->get("provinceCode");
        $municipalityCode = 5306; //$request->get('municipalityCode');
        $position = "PRESIDENT"; //$request->get('position');

        $province = null;
        $municipality = null;

        $province = $em->getRepository("AppBundle:Province")->find($provinceCode);

        if (!$province)
            return new JsonResponse([]);

        $sql = "SELECT * FROM psw_municipality WHERE municipality_code = ? ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipalityCode);
        $stmt->execute();

        $municipality = $stmt->fetch(\PDO::FETCH_ASSOC);

        $results = [];

        $sql = "SELECT 
                (SELECT COUNT(*) FROM tbl_clustered_precinct cp2 WHERE cp2.province_name = ? AND cp2.municipality_name = ?  ) AS total_clustered_precincts,
                (SELECT COALESCE(COUNT(DISTINCT cl_id),0) FROM tbl_clustered_precinct_result cp2 WHERE cp2.province_name = ? AND cp2.municipality_name = ?  ) AS total_result_clustered_precincts,
                (SELECT COALESCE(SUM(cp2.voter_turnout),0) FROM tbl_clustered_precinct cp2 WHERE cp2.province_name = ? AND cp2.municipality_name = ?  ) AS total_voter_casted,
                (SELECT COALESCE(SUM(cp2.norv),0) FROM tbl_clustered_precinct cp2 WHERE cp2.province_name = ? AND cp2.municipality_name = ?  ) AS total_registered_voter,
                SUM(cp.total_turnout) as total_turnout, cp.candidate_name, cp.candidate_position,
                SUM(cp.total_votes) AS total_votes
                FROM tbl_clustered_precinct_result cp 
                WHERE cp.province_name = ? AND cp.municipality_name= ? 
                GROUP BY cp.candidate_position, cp.candidate_name ORDER BY cp.candidate_position ASC, total_votes DESC";

        $stmt = $em->getConnection()->prepare($sql);

        $stmt->bindValue(1, $province->getName());
        $stmt->bindValue(2, $municipality['name']);
        $stmt->bindValue(3, $province->getName());
        $stmt->bindValue(4, $municipality['name']);
        $stmt->bindValue(5, $province->getName());
        $stmt->bindValue(6, $municipality['name']);
        $stmt->bindValue(7, $province->getName());
        $stmt->bindValue(8, $municipality['name']);
        $stmt->bindValue(9, $province->getName());
        $stmt->bindValue(10, $municipality['name']);
        $stmt->execute();

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $sorted = [];

        foreach ($results as $row) {
            $sorted[$row['candidate_position']][] = $row;
        }

        return new JsonResponse($sorted);
    }

    /**
     * @Route("/jpm/ajax_get_member_summary_by_municipality", 
     *       name="ajax_get_member_summary_by_municipality",
     *		options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetMemberByMunicipalitySummary(Request $request)
    {
        $em = $this->getDoctrine()->getManager("province");
        $municipalities = $request->get('municipalities');
        $results = [];

        $municipalityStr = '';

        foreach ($municipalities as $municipality) {
            $municipalityStr .= '"' . $municipality . '",';
        }

        $municipalityStr = substr($municipalityStr, 0, -1);
        $municipalityStr = '(' . $municipalityStr . ')';

        $sql = 'SELECT 
        COALESCE( COUNT(pv.pro_voter_id),0) AS total_members,
        COALESCE(COUNT(CASE WHEN pv.voter_group = "LGC" THEN 1 END),0) AS total_lgc,
        COALESCE(COUNT(CASE WHEN pv.voter_group = "LOPP" THEN 1 END),0) AS total_lopp,
        COALESCE(COUNT(CASE WHEN pv.voter_group = "LPPP" THEN 1 END),0) AS total_lppp,
        COALESCE(COUNT(CASE WHEN pv.voter_group = "LPPP1" THEN 1 END),0) AS total_lppp1,
        COALESCE(COUNT(CASE WHEN pv.voter_group = "LPPP2" THEN 1 END),0) AS total_lppp2,
        COALESCE(COUNT(CASE WHEN pv.voter_group = "LPPP3" THEN 1 END),0) AS total_lppp3
        
        FROM tbl_project_voter pv
        WHERE pv.pro_id = 3 AND pv.elect_id = 4 
        AND pv.has_photo = 1 AND pv.precinct_no IS NOT NULL AND pv.voter_no IS NOT NULL  
        AND pv.voter_group IN ("LGC","LOPP","LPPP","LPPP1","LPPP2","LPPP3") AND pv.municipality_name IN ' . $municipalityStr;

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        $results = $stmt->fetch(\PDO::FETCH_ASSOC);


        return new JsonResponse($results);

    }

    /**
     * @Route("/jpm/ajax_get_member_summary_by_barangay", 
     *       name="ajax_get_member_summary_by_barangay",
     *		options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetMemberByBarangaySummary(Request $request)
    {
        $em = $this->getDoctrine()->getManager("province");
        $municipality = $request->get('municipality');
        $barangays = $request->get('barangays');
        $results = [];

        $barangayStr = "";

        foreach ($barangays as $barangay) {
            $barangayStr .= "'" . $barangay . "',";
        }

        $barangayStr = substr($barangayStr, 0, -1);
        $barangayStr = '(' . $barangayStr . ')';

        $sql = "SELECT 
         COALESCE( COUNT(pv.pro_voter_id),0) AS total_members,
         COALESCE(COUNT(CASE WHEN pv.voter_group = 'LGC' THEN 1 END),0) AS total_lgc,
         COALESCE(COUNT(CASE WHEN pv.voter_group = 'LOPP' THEN 1 END),0) AS total_lopp,
         COALESCE(COUNT(CASE WHEN pv.voter_group = 'LPPP' THEN 1 END),0) AS total_lppp,
         COALESCE(COUNT(CASE WHEN pv.voter_group = 'LPPP1' THEN 1 END),0) AS total_lppp1,
         COALESCE(COUNT(CASE WHEN pv.voter_group = 'LPPP2' THEN 1 END),0) AS total_lppp2,
         COALESCE(COUNT(CASE WHEN pv.voter_group = 'LPPP3' THEN 1 END),0) AS total_lppp3
         
         FROM tbl_project_voter pv
         WHERE pv.pro_id = 3 AND pv.elect_id = 4 
         AND pv.has_photo = 1 AND pv.precinct_no IS NOT NULL AND pv.voter_no IS NOT NULL  
         AND pv.voter_group IN ('LGC','LOPP','LPPP','LPPP1','LPPP2','LPPP3') AND pv.municipality_name = '${municipality}' AND pv.barangay_name IN ${barangayStr}";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        $results = $stmt->fetch(\PDO::FETCH_ASSOC);


        return new JsonResponse($results);
    }


    /**
     * @Route("/jpm/ajax_get_member_summary_by_province", 
     *       name="ajax_get_member_summary_by_province",
     *		options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetMemberProvinceSummary(Request $request)
    {
        $em = $this->getDoctrine()->getManager("province");
        $results = [];

        $sql = "SELECT 
         COALESCE( COUNT(pv.pro_voter_id),0) AS total_members,
         COALESCE(COUNT(CASE WHEN pv.voter_group = 'LGC' THEN 1 END),0) AS total_lgc,
         COALESCE(COUNT(CASE WHEN pv.voter_group = 'LOPP' THEN 1 END),0) AS total_lopp,
         COALESCE(COUNT(CASE WHEN pv.voter_group = 'LPPP' THEN 1 END),0) AS total_lppp,
         COALESCE(COUNT(CASE WHEN pv.voter_group = 'LPPP1' THEN 1 END),0) AS total_lppp1,
         COALESCE(COUNT(CASE WHEN pv.voter_group = 'LPPP2' THEN 1 END),0) AS total_lppp2,
         COALESCE(COUNT(CASE WHEN pv.voter_group = 'LPPP3' THEN 1 END),0) AS total_lppp3
         
         FROM tbl_project_voter pv
         WHERE pv.pro_id = 3 AND pv.elect_id = 4 
         AND pv.has_photo = 1 AND pv.precinct_no IS NOT NULL AND pv.voter_no IS NOT NULL  
         AND pv.voter_group IN ('LGC','LOPP','LPPP','LPPP1','LPPP2','LPPP3') ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        $results = $stmt->fetch(\PDO::FETCH_ASSOC);


        return new JsonResponse($results);

    }

    /**
     * @Route("/jpm/ajax_get_member_summary_by_district", 
     *       name="ajax_get_member_summary_by_district",
     *		options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetMemberByDistrictSummary(Request $request)
    {
        $em = $this->getDoctrine()->getManager("province");
        $district = $request->get('district');
        $results = [];



        $sql = 'SELECT 
        COALESCE( COUNT(pv.pro_voter_id),0) AS total_members,
        COALESCE(COUNT(CASE WHEN pv.voter_group = "LGC" THEN 1 END),0) AS total_lgc,
        COALESCE(COUNT(CASE WHEN pv.voter_group = "LOPP" THEN 1 END),0) AS total_lopp,
        COALESCE(COUNT(CASE WHEN pv.voter_group = "LPPP" THEN 1 END),0) AS total_lppp,
        COALESCE(COUNT(CASE WHEN pv.voter_group = "LPPP1" THEN 1 END),0) AS total_lppp1,
        COALESCE(COUNT(CASE WHEN pv.voter_group = "LPPP2" THEN 1 END),0) AS total_lppp2,
        COALESCE(COUNT(CASE WHEN pv.voter_group = "LPPP3" THEN 1 END),0) AS total_lppp3,
        m.district
        FROM tbl_project_voter pv 
        INNER JOIN psw_municipality m ON pv.municipality_no = m.municipality_no AND m.province_code = 53 
        WHERE pv.pro_id = 3 AND pv.elect_id = 4 
        AND pv.has_photo = 1 AND pv.precinct_no IS NOT NULL AND pv.voter_no IS NOT NULL  
        AND pv.voter_group IN ("LGC","LOPP","LPPP","LPPP1","LPPP2","LPPP3") AND m.district = ? ';

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $district);
        $stmt->execute();

        $results = $stmt->fetch(\PDO::FETCH_ASSOC);


        return new JsonResponse($results);

    }

    /**
     * @Route("/ajax_m_get_project_voters_canlaon",
     *       name="ajax_m_get_project_voters_canlaon",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetJpmProjectVotersCanlaon(Request $request)
    {
        $em = $this->getDoctrine()->getManager("canlaon");

        $municipalityName = $request->get('municipalityName');
        $barangayName = $request->get('barangayName');

        $voterName = $request->get("voterName");

        $is1 = $request->get("is1");
        $is2 = $request->get("is2");
        $is3 = $request->get("is3");
        $is4 = $request->get("is4");
        $is5 = $request->get("is5");
        $is6 = $request->get("is6");
        $is7 = $request->get("is7");
        $is8 = $request->get("is8");


        $batchSize = 10;
        $batchNo = $request->get("batchNo");

        $batchOffset = $batchNo * $batchSize;

        $sql = "SELECT pv.* FROM tbl_project_voter pv WHERE 1 AND ";

        if (!is_numeric($voterName)) {
            $sql .= " (pv.voter_name LIKE ? OR ? IS NULL ) ";
        } else {
            $sql .= " (pv.generated_id_no LIKE ? OR ? IS NULL ) ";
        }

        if ($is1 == 1) {
            $sql .= "AND pv.is_1 = 1 ";
        } else if ($is2 == 1) {
            $sql .= "AND pv.is_2 = 1 ";
        } else if ($is3 == 1) {
            $sql .= "AND pv.is_3 = 1 ";
        } else if ($is4 == 1) {
            $sql .= "AND pv.is_4 = 1 ";
        } else if ($is5 == 1) {
            $sql .= "AND pv.is_5 = 1 ";
        } else if ($is6 == 1) {
            $sql .= "AND pv.is_6 = 1 ";
        } else if ($is7 == 1) {
            $sql .= "AND pv.is_7 = 1 ";
        } else if ($is8 == 1) {
            $sql .= "AND pv.is_8 = 1 ";
        }


        $sql .= "  AND (pv.municipality_name LIKE ? OR ? IS NULL) 
                    AND (pv.barangay_name LIKE ? OR ? IS NULL) 
                    AND pv.precinct_no IS NOT NULL 
                    ORDER BY pv.voter_name ASC LIMIT {$batchSize} OFFSET {$batchOffset}";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, '%' . $voterName . '%');
        $stmt->bindValue(2, empty($voterName) ? null : '%' . $voterName . '%');
        $stmt->bindValue(3, '%' . $municipalityName . '%');
        $stmt->bindValue(4, empty($municipalityName) ? null : '%' . $municipalityName . '%');
        $stmt->bindValue(5, '%' . $barangayName . '%');
        $stmt->bindValue(6, empty($barangayName) ? null : '%' . $barangayName . '%');
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_m_patch_project_voter_canlaon/{proVoterId}",
     *     name="ajax_m_patch_project_voter_canlaon",
     *    options={"expose" = true}
     * )
     * @Method("PATCH")
     */

    public function ajaxPatchProjectVoterCanlaonAction($proVoterId, Request $request)
    {
        $em = $this->getDoctrine()->getManager("canlaon");

        $data = json_decode($request->getContent(), true);
        $request->request->replace($data);

        $is1 = $request->get('is1');
        $is2 = $request->get('is2');
        $is3 = $request->get('is3');
        $is4 = $request->get('is4');
        $is5 = $request->get('is5');
        $is6 = $request->get('is6');
        $is7 = $request->get('is7');
        $is8 = $request->get('is8');

        $cellphoneNo = $request->get('cellphoneNo');
        $birthdate = $request->get('birthdate');

        $sql = "UPDATE tbl_project_voter SET is_1 = ? , is_2 = ? , is_3 = ? , is_4 = ? , is_5 = ? , is_6 = ?, is_7 = ? , is_8 = ?, cellphone = ?, birthdate = ?
                WHERE pro_voter_id = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $is1);
        $stmt->bindValue(2, $is2);
        $stmt->bindValue(3, $is3);
        $stmt->bindValue(4, $is4);
        $stmt->bindValue(5, $is5);
        $stmt->bindValue(6, $is6);
        $stmt->bindValue(7, $is7);
        $stmt->bindValue(8, $is8);
        $stmt->bindValue(9, $cellphoneNo);
        $stmt->bindValue(10, $birthdate);
        $stmt->bindValue(11, $proVoterId);
        $stmt->execute();

        return new JsonResponse(200);
    }


    /**
     * @Route("/ajax_m_get_canlaon_barangay_summary",
     *       name="ajax_m_get_canlaon_barangay_summary",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetCanlaonBarangaySummary(Request $request)
    {
        $em = $this->getDoctrine()->getManager("canlaon");

        $sql = "SELECT 
                    barangay_name,
                    COALESCE(SUM( CASE WHEN pv.is_1 = 1 THEN 1 ELSE 0 END),0) AS total_1,
                    COALESCE(SUM( CASE WHEN pv.is_2 = 1 THEN 1 ELSE 0 END),0) AS total_2,
                    COALESCE(SUM( CASE WHEN pv.is_3 = 1 THEN 1 ELSE 0 END),0) AS total_3,
                    COALESCE(SUM( CASE WHEN pv.is_4 = 1 THEN 1 ELSE 0 END),0) AS total_4,
                    COALESCE(SUM( CASE WHEN pv.is_5 = 1 THEN 1 ELSE 0 END),0) AS total_5,
                    COALESCE(SUM( CASE WHEN pv.is_6 = 1 THEN 1 ELSE 0 END),0) AS total_6,
                    COALESCE(SUM( CASE WHEN pv.is_7 = 1 THEN 1 ELSE 0 END),0) AS total_7,
                    COALESCE(SUM( CASE WHEN pv.is_8 = 1 THEN 1 ELSE 0 END),0) AS total_8,
                    COALESCE(COUNT(pv.pro_voter_id ),0) AS total_voter
                    
                    FROM tbl_project_voter pv GROUP  BY barangay_name ORDER BY barangay_name";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_download_canlcaon_barangay_summary_excel",
     *     name="ajax_download_canlcaon_barangay_summary_excel",
     *     options={"expose" = true}
     *     )
     * @Method("GET")
     */

    public function ajaxDownloadCanlaonBarangaySummaryExcel(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager("canlaon");

        $filename = md5(uniqid(rand(), true)) . ".xlsx";
        $fileRoot = __DIR__ . '/../../../web/uploads/exports/';

        $defaultStyle = (new StyleBuilder())
            ->setFontName('Arial')
            ->setFontSize(11)
            ->build();

        $headingStyle = (new StyleBuilder())
            ->setFontName('Arial')
            ->setFontSize(11)
            ->setFontBold()
            ->setShouldWrapText(false)
            ->build();

        $writer = WriterFactory::create(Type::XLSX);
        $writer->setDefaultRowStyle($defaultStyle);
        $writer->openToFile($fileRoot . $filename);
        $writer->addRowWithStyle(['BARANGAY', 'NORV', 'TOTAL', '1', '2', '3', '4', '5', '6', '7', '8'], $headingStyle);


        $sql = "SELECT 
        barangay_name,
        COALESCE(SUM( CASE WHEN pv.is_1 = 1 THEN 1 ELSE 0 END),0) AS total_1,
        COALESCE(SUM( CASE WHEN pv.is_2 = 1 THEN 1 ELSE 0 END),0) AS total_2,
        COALESCE(SUM( CASE WHEN pv.is_3 = 1 THEN 1 ELSE 0 END),0) AS total_3,
        COALESCE(SUM( CASE WHEN pv.is_4 = 1 THEN 1 ELSE 0 END),0) AS total_4,
        COALESCE(SUM( CASE WHEN pv.is_5 = 1 THEN 1 ELSE 0 END),0) AS total_5,
        COALESCE(SUM( CASE WHEN pv.is_6 = 1 THEN 1 ELSE 0 END),0) AS total_6,
        COALESCE(SUM( CASE WHEN pv.is_7 = 1 THEN 1 ELSE 0 END),0) AS total_7,
        COALESCE(SUM( CASE WHEN pv.is_8 = 1 THEN 1 ELSE 0 END),0) AS total_8,
        COALESCE(COUNT(pv.pro_voter_id ),0) AS total_voter
        
        FROM tbl_project_voter pv GROUP  BY barangay_name ORDER BY barangay_name";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        $data = [];
        $g1 = 0;
        $g2 = 0;
        $g3 = 0;
        $g4 = 0;
        $g5 = 0;
        $g6 = 0;
        $g7 = 0;
        $g8 = 0;
        $gtotal = 0;
        $gnorv = 0;

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $total = 0;
            $total = $row['total_1'];
            $total += $row['total_2'];
            $total += $row['total_3'];
            $total += $row['total_4'];
            $total += $row['total_5'];
            $total += $row['total_6'];
            $total += $row['total_7'];
            $total += $row['total_8'];

            $g1 += $row['total_1'];
            $g2 += $row['total_2'];
            $g3 += $row['total_3'];
            $g4 += $row['total_4'];
            $g5 += $row['total_5'];
            $g6 += $row['total_6'];
            $g7 += $row['total_7'];
            $g8 += $row['total_8'];
            $gtotal += $total;
            $gnorv += $row['total_voter'];

            $writer->addRow([
                $row['barangay_name'],
                $row['total_voter'] == 0 ? "" : number_format($row['total_voter']),
                $total == 0 ? "" : number_format($total),
                $row['total_1'] == 0 ? "" : $row['total_1'],
                $row['total_2'] == 0 ? "" : $row['total_2'],
                $row['total_3'] == 0 ? "" : $row['total_3'],
                $row['total_4'] == 0 ? "" : $row['total_4'],
                $row['total_5'] == 0 ? "" : $row['total_5'],
                $row['total_6'] == 0 ? "" : $row['total_6'],
                $row['total_7'] == 0 ? "" : $row['total_7'],
                $row['total_8'] == 0 ? "" : $row['total_8']
            ]);
        }

        $writer->addRow([
            "Grand Total",
            $gnorv == 0 ? "" : number_format($gnorv),
            $gtotal == 0 ? "" : number_format($gtotal),
            $g1 == 0 ? "" : $g1,
            $g2 == 0 ? "" : $g2,
            $g3 == 0 ? "" : $g3,
            $g4 == 0 ? "" : $g4,
            $g5 == 0 ? "" : $g5,
            $g6 == 0 ? "" : $g6,
            $g7 == 0 ? "" : $g7,
            $g8 == 0 ? "" : $g8
        ]);

        $writer->close();

        $response = new BinaryFileResponse($fileRoot . $filename);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        return $response;
    }


    /**
     * @Route("/ajax_m_get_deactivated_profiles",
     *       name="ajax_m_get_deactivated_profiles",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetDeactivatedProfiles(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $batchSize = 10;
        $batchNo = $request->get("batchNo");
        $voterName = $request->get("voterName");
        $barangayName = $request->get('barangayName');

        $batchOffset = $batchNo * $batchSize;

        $sql = "SELECT pv.*
         FROM tbl_project_voter pv
         WHERE (pv.voter_name LIKE ? OR ? IS NULL ) 
         AND (pv.barangay_name = ? OR ? IS NULL) 
         AND pv.is_on_hold = 1
         ORDER BY pv.voter_name ASC LIMIT {$batchSize} OFFSET {$batchOffset}";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, '%' . strtoupper(trim($voterName)) . '%');
        $stmt->bindValue(2, empty($voterName) ? null : $voterName);
        $stmt->bindValue(3, strtoupper(trim($barangayName)));
        $stmt->bindValue(4, empty($barangayName) ? null : $barangayName);
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['cellphone_no'] = $row['cellphone'];
            $data[] = $row;
        }

        return new JsonResponse([
            "data" => $data
        ]);
    }


    /**
     * @Route("/ajax_m_deactivate_profile/{proVoterId}",
     *       name="ajax_m_deactivate_profile",
     *        options={ "expose" = true }
     * )
     * @Method("POST")
     */

    public function ajaxDeactivateProfile(Request $request, $proVoterId)
    {
        $em = $this->getDoctrine()->getManager();

        $projectVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy(['proVoterId' => $proVoterId]);

        if ($projectVoter) {

            if ($projectVoter->getStatus() != 'A') {
                return new JsonResponse(['message' => "Opps! Action denied... Voter either blocked or deactivated..."], 400);
            }

            $projectVoter->setStatus('DEACTIVATED');
            $projectVoter->setIsOnHold(1);
            $projectVoter->setOldVoterGroup($projectVoter->getVoterGroup());
            $projectVoter->setVoterGroup("");
        }

        $em->flush();
        $em->clear();

        $serializer = $this->get('serializer');
        $projectVoter = $serializer->normalize($projectVoter);

        return new JsonResponse($projectVoter);
    }


    /**
     * @Route("/ajax_m_get_elect_prep_2024_project_voters",
     *       name="ajax_m_get_elect_prep_2024_project_voters",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetElectPrep2024ProjectVoters(Request $request)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");

        $provinceCode = $request->get('provinceCode');
        $municipalityNo = $request->get('municipalityNo');
        $municipalityName = $request->get('municipalityName');
        $barangayName = $request->get('barangayName');
        $voterGroup = $request->get('voterGroup');

        $brgyNo = $request->get("brgyNo");
        $voterName = $request->get("voterName");
        $imgUrl = $this->getParameter('img_url');
        $batchSize = 10;
        $batchNo = $request->get("batchNo");

        $batchOffset = $batchNo * $batchSize;

        $sql = "SELECT pv.* FROM tbl_project_voter pv WHERE 1 AND ";

        if (!is_numeric($voterName)) {
            $sql .= " (pv.voter_name LIKE ? OR ? IS NULL ) ";
        } else {
            $sql .= " (pv.generated_id_no LIKE ? OR ? IS NULL ) ";
        }

        $sql .= "AND pv.elect_id = ? 
         AND (pv.municipality_name LIKE ? OR ? IS NULL) 
         AND (pv.barangay_name LIKE ? OR ? IS NULL) 
         AND pv.precinct_no IS NOT NULL 
         ORDER BY pv.voter_name ASC LIMIT {$batchSize} OFFSET {$batchOffset}";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, '%' . $voterName . '%');
        $stmt->bindValue(2, empty($voterName) ? null : '%' . $voterName . '%');
        $stmt->bindValue(3, self::ACTIVE_ELECTION);
        $stmt->bindValue(4, '%' . $municipalityName . '%');
        $stmt->bindValue(5, empty($municipalityName) ? null : '%' . $municipalityName . '%');
        $stmt->bindValue(6, '%' . $barangayName . '%');
        $stmt->bindValue(7, empty($barangayName) ? null : '%' . $barangayName . '%');
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['imgUrl'] = $imgUrl . '3_' . $row['generated_id_no'] . '?' . strtotime((new \DateTime())->format('Y-m-d H:i:s'));
            $row['cellphone_no'] = $row['cellphone'];
            $data[] = $row;
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_patch_elect_prep_2024_has_attended/{proVoterId}/{hasAttended}",
     *     name="ajax_patch_elect_prep_2024_has_attended",
     *    options={"expose" = true}
     * )
     * @Method("PATCH")
     */

    public function ajaxPatchElectPrep2024HasAttendedAction($proVoterId, $hasAttended, Request $request)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->find($proVoterId);

        if (!$proVoter) {
            return new JsonResponse([], 404);
        }

        $proVoter->setHasAttended($hasAttended);
        $proVoter->setDidChanged(1);

        $validator = $this->get('validator');
        $violations = $validator->validate($proVoter);

        $errors = [];

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse($errors, 400);
        }

        $em->flush();
        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($proVoter));
    }

    /**
     * BCBP FUNCTIONS
     */

    /**
     * @Route("/ajax_m_get_bcbp_profiles",
     *       name="ajax_m_get_bcbp_profiles",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetBcbpProfiles(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $name = $request->get('name');

        $batchSize = 10;
        $batchNo = $request->get("batchNo");

        $batchOffset = $batchNo * $batchSize;

        $sql = "SELECT b.* FROM tbl_temp_bcbp_profile b WHERE 1 AND ";

        if (!is_numeric($name)) {
            $sql .= " (b.name LIKE ? OR ? IS NULL ) ";
        }

        $sql .= " ORDER BY b.name ASC LIMIT {$batchSize} OFFSET {$batchOffset}";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, '%' . $name . '%');
        $stmt->bindValue(2, empty($name) ? null : '%' . $name . '%');
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_m_get_bcbp_events",
     *       name="ajax_m_get_bcbp_events",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetBcbpEvents(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT h.* ,
                (SELECT COALESCE(COUNT(*),0) FROM tbl_bcbp_event_detail d WHERE d.event_id = h.id AND d.has_attended = 1 ) as total_attended
                FROM tbl_bcbp_event_header h
                WHERE (h.event_description LIKE ? OR ? IS NULL) ORDER BY h.id DESC LIMIT 30";
        $name = $request->get("name");

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, '%' . $name . '%');
        $stmt->bindValue(2, empty($name) ? null : $name);
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_m_post_bcbp_event",
     *       name="ajax_m_post_bcbp_event",
     *        options={ "expose" = true }
     * )
     * @Method("POST")
     */

    public function ajaxPostBcbpEvent(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = new BcbpEventHeader();
        $entity->setEventDescription($request->get('eventDescription'));
        $entity->setEventDate($request->get('eventDate'));
        $entity->setEventType($request->get('eventType'));


        $validator = $this->get('validator');
        $violations = $validator->validate($entity);

        $errors = [];

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse($errors, 400);
        }

        $entity->setStatus('A');

        $em->persist($entity);
        $em->flush();

        $sql = "SELECT * FROM tbl_bcbp_members";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        foreach ($data as $row) {
            $sql = "INSERT INTO tbl_bcbp_event_detail(
                member_id,
                event_id,
                has_attended,
                status
            )
            VALUES(?,?,?,?)
            ";

            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $row['id']);
            $stmt->bindValue(2, $entity->getId());
            $stmt->bindValue(3, 0);
            $stmt->bindValue(4, 'A');
            $stmt->execute();
        }

        $em->clear();

        $serializer = $this->get('serializer');
        $entity = $serializer->normalize($entity);

        return new JsonResponse($entity);
    }

    /**
     * @Route("/ajax_m_get_bcbp_event_attendees/{eventId}",
     *       name="ajax_m_get_bcbp_event_attendees",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetBcbpEventAttendees($eventId, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $unitFilter = $request->get("unitFilter");
        $agFilter = $request->get("agFilter");
        $genderFilter = $request->get("genderFilter");
        $attendedFilter = $request->get("attendedFilter");

        $unitFilter = $unitFilter == 'ALL' ? "" : $unitFilter;
        $agFilter = $agFilter == 'ALL' ? "" : $agFilter;
        $genderFilter = $genderFilter == 'ALL' ? "" : $genderFilter;

        if ($attendedFilter == 'ATTENDED') {
            $attended = 1;
        } else if ($attendedFilter == 'NOT_ATTENDED') {
            $attended = 0;
        } else {
            $attended = -1;
        }

        $sql = "SELECT 
        (SELECT COUNT(*) FROM tbl_bcbp_event_detail dd WHERE dd.event_id = ? ) AS total_attendees,
	    (SELECT COUNT(*) FROM tbl_bcbp_event_detail dd 
        INNER JOIN tbl_bcbp_members mm
        ON dd.member_id = mm.id 
        WHERE dd.event_id = ? AND dd.has_attended = 1 
        AND (mm.unit_no = ? OR ? IS NULL)
        AND (mm.ag_no = ? OR ? IS NULL) 
        AND (mm.gender = ? OR ? IS NULL)
        ) AS total_attended,
       
        m.*, d.id as dtl_id , d.has_attended 
        FROM tbl_bcbp_event_detail d INNER JOIN tbl_bcbp_members m
        ON d.member_id = m.id 
        WHERE d.event_id = ? 
        AND (d.has_attended = ? OR ? IS NULL)
        AND (m.unit_no = ? OR ? IS NULL)
        AND (m.ag_no = ? OR ? IS NULL)
        AND (m.gender = ? OR ? IS NULL)
        AND m.status = 'A'
        ORDER BY m.name ASC";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $eventId);
        $stmt->bindValue(2, $eventId);
        $stmt->bindValue(3, $unitFilter);
        $stmt->bindValue(4, $unitFilter == "" ? null : $unitFilter);
        $stmt->bindValue(5, $agFilter);
        $stmt->bindValue(6, $agFilter == "" ? null : $agFilter);
        $stmt->bindValue(7, $genderFilter);
        $stmt->bindValue(8, $genderFilter == "" ? null : $genderFilter);

        $stmt->bindValue(9, $eventId);
        $stmt->bindValue(10, $attended);
        $stmt->bindValue(11, $attended == -1 ? null : $attended);
        $stmt->bindValue(12, $unitFilter);
        $stmt->bindValue(13, $unitFilter == "" ? null : $unitFilter);
        $stmt->bindValue(14, $agFilter);
        $stmt->bindValue(15, $agFilter == "" ? null : $agFilter);
        $stmt->bindValue(16, $genderFilter);
        $stmt->bindValue(17, $genderFilter == "" ? null : $genderFilter);
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_m_get_bcbp_members",
     *       name="ajax_m_get_bcbp_members",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetBcbpMembers(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = $request->get("searchText");

        $sql = "SELECT * 
                 FROM tbl_bcbp_members m 
                 WHERE (m.name like ? OR ? IS NULL)
                 ORDER BY name ASC";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, '%' . $searchText . '%');
        $stmt->bindValue(2, $searchText == "" ? null : $searchText);
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_patch_bcbp_event_has_attended/{dtlId}/{hasAttended}",
     *     name="ajax_patch_bcbp_event_has_attended",
     *    options={"expose" = true}
     * )
     * @Method("PATCH")
     */

    public function ajaxPatchBcbpEventHasAttendedAction($dtlId, $hasAttended, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $entity = $em->getRepository("AppBundle:BcbpEventDetail")->find($dtlId);

        if (!$entity) {
            return new JsonResponse([], 404);
        }

        $entity->setHasAttended($hasAttended);

        $validator = $this->get('validator');
        $violations = $validator->validate($entity);

        $errors = [];

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse($errors, 400);
        }

        $em->flush();
        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($entity));
    }



    /**
     * @Route("/ajax_patch_bcbp_status/{id}/{newStatus}",
     *     name="ajax_patch_bcbp_status",
     *    options={"expose" = true}
     * )
     * @Method("PATCH")
     */

    public function ajaxPatchBcbpStatus($id, $newStatus, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $entity = $em->getRepository("AppBundle:BcbpMember")->find($id);

        if (!$entity) {
            return new JsonResponse([], 404);
        }

        $entity->setStatus($newStatus);

        $validator = $this->get('validator');
        $violations = $validator->validate($entity);

        $errors = [];

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse($errors, 400);
        }

        $em->flush();
        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($entity));
    }

    /**
     * @Route("/ajax_m_post_bcbp_member",
     *       name="ajax_m_post_bcbp_member",
     *        options={ "expose" = true }
     * )
     * @Method("POST")
     */

    public function ajaxPostBcbpMember(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = new BcbpMember();
        $entity->setName($request->get('memberName'));
        $entity->setPosition($request->get('memberPosition'));
        $entity->setUnitNo($request->get('memberUnit'));
        $entity->setAgNo($request->get('memberGroup'));
        $entity->setTUnits($request->get('memberTights'));

        $validator = $this->get('validator');
        $violations = $validator->validate($entity);

        $errors = [];

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse($errors, 400);
        }

        $entity->setStatus('A');

        $em->persist($entity);
        $em->flush();


        $serializer = $this->get('serializer');
        $entity = $serializer->normalize($entity);

        return new JsonResponse($entity);
    }

    /**
     * @Route("/ajax_delete_bcbp_event/{eventId}",
     *     name="ajax_delete_bcbp_event",
     *    options={"expose" = true}
     * )
     * @Method("DELETE")
     */

    public function ajaxDeleteBcbpEventAction($eventId, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $header = $em->getRepository("AppBundle:BcbpEventHeader")->find($eventId);

        if (!$header) {
            return new JsonResponse(null, 404);
        }

        $sql = "DELETE FROM tbl_bcbp_event_detail WHERE event_id = ? ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $eventId);
        $stmt->execute();

        $em->remove($header);
        $em->flush();

        return new JsonResponse(null, 200);
    }

    /**
     * Ako Palawan Functions
     */

    /**
     * @Route("/ajax_m_get_ap_active_event_attendees",
     *       name="ajax_m_get_ap_active_event_attendees",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetApActiveEventAttendees(Request $request)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");
        $imgUrl = $this->getParameter('img_url');

        $batchSize = 10;
        $batchNo = $request->get("batchNo");
        $searchText = $request->get("searchText");
        $showLinked = $request->get("showLinked");
        $eventId = $request->get('eventId');
        $barangayName = $request->get('barangayName');

        $showLinked = $showLinked == 'true' ? true : false;

        $batchOffset = $batchNo * $batchSize;

        if ($showLinked) {
            $sql = "SELECT ac.* FROM tbl_ap_card ac
                    INNER JOIN tbl_project_voter pv 
                    ON pv.pro_voter_id = ac.pro_voter_id
                    WHERE ((ac.qr_code_no LIKE ? OR ac.card_no LIKE ?) OR ? IS NULL) 
                    AND (ac.pro_voter_id IS NOT NULL AND ac.pro_voter_id <> '')  
                    LIMIT 100 ";
        } else {
            $sql = "SELECT * FROM tbl_ap_card WHERE (qr_code_no LIKE ? OR card_no LIKE ?) OR ? IS NULL LIMIT 100 ";
        }


        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, '%' . $searchText . '%');
        $stmt->bindValue(2, '%' . $searchText . '%');
        $stmt->bindValue(3, empty($searchText) ? null : $searchText);
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }
        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_m_get_ap_profile_by_qr_code/{qrCode}",
     *       name="ajax_m_get_ap_profile_by_qr_code",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetApProfileByQrCode(Request $request, $qrCode)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $entity = $em->getRepository("AppBundle:ApCard")->findOneBy(["qrCodeNo" => $qrCode]);

        if (!$entity) {
            return new JsonResponse([], 404);
        }

        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($entity));
    }

    /**
     * @Route("/ajax_ap_link_profile/{qrCodeNo}/{proVoterId}",
     *     name="ajax_ap_link_profile",
     *    options={"expose" = true}
     * )
     * @Method("POST")
     */

    public function ajaxApLinkProfileAction($qrCodeNo, $proVoterId, Request $request)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->find($proVoterId);
        $apCard = $em->getRepository("AppBundle:ApCard")->findOneBy(['qrCodeNo' => $qrCodeNo, "cardNo" => $request->get('cardNo')]);

        if (!$proVoter || !$apCard) {
            return new JsonResponse([], 404);
        }

        $apCard->setProVoterId($proVoter->getProVoterId());
        $apCard->setProIdCode($proVoter->getProVoterId());
        $apCard->setGeneratedIdNo($proVoter->getGeneratedIdNo());
        $apCard->setVoterName($proVoter->getVoterName());
        $apCard->setBarangayName($proVoter->getBarangayName());
        $apCard->setMunicipalityName($proVoter->getMunicipalityName());
        $apCard->setDateActivated(date('Y-m-d'));

        $validator = $this->get('validator');
        $violations = $validator->validate($apCard);

        $errors = [];

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse($errors, 400);
        }

        $em->flush();
        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($apCard));
    }

    /**
     * @Route("/ajax_ap_update_profile/{qrCodeNo}",
     *     name="ajax_ap_update_profile",
     *    options={"expose" = true}
     * )
     * @Method("POST")
     */

    public function ajaxApUpdateProfileAction($qrCodeNo, Request $request)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $apCard = $em->getRepository("AppBundle:ApCard")->findOneBy(['qrCodeNo' => $qrCodeNo]);

        if (!$apCard) {
            return new JsonResponse([], 404);
        }

        $apCard->setContactNo($request->get('contactNo'));
        $apCard->setRemarks($request->get("remarks"));

        $validator = $this->get('validator');
        $violations = $validator->validate($apCard);

        $errors = [];

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse($errors, 400);
        }

        $em->flush();
        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($apCard));
    }

    /**
     * @Route("/ajax_ap_upload_profile_photo/{qrCodeNo}",
     *     name="ajax_ap_upload_profile_photo",
     *     options={"expose" = true}
     *     )
     * @Method("POST")
     */

    public function ajaxUploadApProfilePhoto(Request $request, $qrCodeNo)
    {
        $em = $this->getDoctrine()->getManager("electPrep2024");

        $entity = $em->getRepository("AppBundle:ApCard")
            ->findOneBy(['qrCodeNo' => $qrCodeNo]);

        if (!$entity) {
            return new JsonResponse(['message' => 'not found'], 404);
        }

        $serializer = $this->get('serializer');

        $images = $request->files->get('files');
        $filename = $entity->getQrCodeNo() . '.jpg';
        $imgRoot = __DIR__ . '/../../../web/uploads/ako-palawan/';
        $imagePath = $imgRoot . $filename;

        $data = json_decode($request->getContent(), true);
        $this->compress(base64_decode($data['photo']), $imagePath, 30);

        $em->flush();
        $em->clear();

        return new JsonResponse(null, 200);
    }

    /**
     * @Route("/ap/photo/{qrCodeNo}",
     *   name="ajax_get_ap_profile_photo",
     *   options={"expose" = true}
     * )
     * @Method("GET")
     */

    public function ajaxGetApProfilePhotoAction($qrCodeNo)
    {

        $rootDir = __DIR__ . '/../../../web/uploads/ako-palawan/';
        $imagePath = $rootDir . $qrCodeNo . '.jpg';

        if (!file_exists($imagePath)) {
            $imagePath = $rootDir . 'default.jpg';
        }

        $response = new BinaryFileResponse($imagePath);
        $response->headers->set('Content-Type', 'image/jpeg');

        return $response;
    }

    /**
     * @Route("/ajax_m_get_household_profile/{householdCode}",
     *       name="ajax_m_get_household_profile",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

     public function ajaxGetHouseholdProfile(Request $request, $householdCode)
     {
 
         $em = $this->getDoctrine()->getManager("electPrep2024");
 
         $sql = "SELECT hh.voter_name, hh.household_code,hh.household_no, hh.municipality_name, hh.barangay_name,hh.id , pv.is_non_voter, pv.precinct_no,
                (SELECT COALESCE(COUNT(hd.id),0) FROM tbl_household_dtl hd WHERE hh.id = hd.household_id) AS total_members,
                (SELECT COALESCE(COUNT(hd.id),0) FROM tbl_household_dtl hd INNER JOIN tbl_project_voter ppv ON ppv.pro_voter_id = hd.pro_voter_id WHERE hh.id = hd.household_id AND ppv.is_non_voter = 0 ) AS total_voter_members,
                (SELECT COALESCE(COUNT(hd.id),0) FROM tbl_household_dtl hd INNER JOIN tbl_project_voter ppv ON ppv.pro_voter_id = hd.pro_voter_id WHERE hh.id = hd.household_id AND ppv.is_non_voter = 1 ) AS total_non_voter_members
                FROM tbl_household_hdr hh INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = hh.pro_voter_id WHERE household_code = ? ";
 
         $stmt = $em->getConnection()->prepare($sql);
         $stmt->bindValue(1, $householdCode);
         $stmt->execute();

         $hdr = $stmt->fetch(\PDO::FETCH_ASSOC);

         if(!$hdr)
            return new JsonResponse(['message' => 'Household not found. Please contact the system administrator'], 404);

        $sql = "SELECT pv.voter_name,pv.municipality_name,pv.barangay_name,pv.is_non_voter,pv.precinct_no FROM tbl_household_dtl hd INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = hd.pro_voter_id  
                WHERE hd.household_id = ? ORDER BY voter_name ASC ";
 
         $stmt = $em->getConnection()->prepare($sql);
         $stmt->bindValue(1, $hdr['id']);
         $stmt->execute();

        $dtls = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $hdr['members'] = $dtls;

        $hdr['total_members'] = $hdr['total_members'] + 1;
        $hdr['total_voter_members'] = $hdr['is_non_voter'] == 0 ? $hdr['total_voter_members'] + 1 : $hdr['total_voter_members'];
        $hdr['total_non_voter_members'] = $hdr['is_non_voter'] == 1 ? $hdr['total_non_voter_members'] + 1 : $hdr['total_non_voter_members'];

         return new JsonResponse($hdr);
     }
}