<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @Route("/ako-palawan")
 */

class AkoPalawanController extends Controller
{
    /**
     * @Route("/ajax_ako_palawan_generate_card/{year}/{month}/{day}",
     *       name="ajax_ako_palawan_generate_card",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxAkoPalawanGenerateCard($year, $month, $day)
    {
        $em = $this->getDoctrine()->getManager();

        $response = new StreamedResponse();
        $response->headers->set("Cache-Control", "no-cache, must-revalidate");
        $response->headers->set("X-Accel-Buffering", "no");
        $response->setStatusCode(200);

        $response->setCallback(function () use ($em, $year, $month, $day) {

            $counter = 0;

            while ($counter < 3553) {

                $card_number = mt_rand(10000000, 99999999);
                $sql = "select * from tbl_ap_card where card_no = ? ";
                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $card_number);
                $stmt->execute();

                $row = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                if (!$row) {
                    $qr_number = $this->generateRandomString(8);

                    $sql = "select * from tbl_ap_card where qr_code_no = ? ";
                    $stmt = $em->getConnection()->prepare($sql);
                    $stmt->bindValue(1, $qr_number);
                    $stmt->execute();

                    $row = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                    if(!$row){

                        $sql = "INSERT INTO tbl_ap_card(
                            qr_code_no,card_no,date_generated,year_generated
                        ) VALUES(?,?,?,?) ";

                        $stmt = $em->getConnection()->prepare($sql);
                        $stmt->bindValue(1, $qr_number);
                        $stmt->bindValue(2, $card_number);
                        $stmt->bindValue(3, $year . '-' . $month . '-' . $day);
                        $stmt->bindValue(4, $year);
                        $stmt->execute();

                        $counter++;

                        echo $counter . '. QR No. : ' .  $qr_number . ' Card No. : ' . $card_number . '<br/>';

                        ob_flush();
                        flush();
                    }
                }

            }

        });

        $em->clear();

        return $response;
    }

    function generateRandomString($length = 8)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
