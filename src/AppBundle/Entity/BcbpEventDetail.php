<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * BcbpEventDetail
 *
 * @ORM\Table(name="tbl_bcbp_event_detail")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BcbpEventDetailRepository")
 * @UniqueEntity(fields={"eventId","memberId"},message="This member already exists.", errorPath="memberId")
 */
class BcbpEventDetail
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="member_id", type="integer")
     * @Assert\NotBlank()
     */
    private $memberId;

    /**
     * @var int
     *
     * @ORM\Column(name="event_id", type="integer")
     * @Assert\NotBlank()
     */
    private $eventId;

    /**
     * @var int
     *
     * @ORM\Column(name="has_attended", type="integer")
     */
    private $hasAttended;

    /**
     * @var string
     *
     * @ORM\Column(name="remarks", type="string", length=255)
     */
    private $remarks;
    
    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=3)
     */
    private $status;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set memberId
     *
     * @param integer $memberId
     *
     * @return BcbpEventDetail
     */
    public function setMemberId($memberId)
    {
        $this->memberId = $memberId;

        return $this;
    }

    /**
     * Get memberId
     *
     * @return integer
     */
    public function getMemberId()
    {
        return $this->memberId;
    }

    /**
     * Set eventId
     *
     * @param integer $eventId
     *
     * @return BcbpEventDetail
     */
    public function setEventId($eventId)
    {
        $this->eventId = $eventId;

        return $this;
    }

    /**
     * Get eventId
     *
     * @return integer
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * Set hasAttended
     *
     * @param integer $hasAttended
     *
     * @return BcbpEventDetail
     */
    public function setHasAttended($hasAttended)
    {
        $this->hasAttended = $hasAttended;

        return $this;
    }

    /**
     * Get hasAttended
     *
     * @return integer
     */
    public function getHasAttended()
    {
        return $this->hasAttended;
    }

    /**
     * Set remarks
     *
     * @param string $remarks
     *
     * @return BcbpEventDetail
     */
    public function setRemarks($remarks)
    {
        $this->remarks = $remarks;

        return $this;
    }

    /**
     * Get remarks
     *
     * @return string
     */
    public function getRemarks()
    {
        return $this->remarks;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return BcbpEventDetail
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
}
