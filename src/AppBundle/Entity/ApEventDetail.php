<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * ApEventDetail
 *
 * @ORM\Table(name="tbl_ap_event_detail")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProjectEventDetailRepository")
 * @UniqueEntity(fields={"proVoterId","eventId"},message="This attendee already added on this event.", errorPath="proVoterId")
 */
class ApEventDetail
{
    /**
     * @var int
     *
     * @ORM\Column(name="event_detail_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $eventDetailId;
    
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
     * @ORM\Column(name="pro_voter_id", type="integer")
     * @Assert\NotBlank()
     */
    private $proVoterId;

    /**
     * @var string
     *
     * @ORM\Column(name="pro_id_code", type="string", length=30)
     * @Assert\NotBlank()
     */
    private $proIdCode;

     /**
     * @var int
     *
     * @ORM\Column(name="has_attended", type="integer")
     */
    private $hasAttended;

     /**
     * @var datetime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var datetime
     *
     * @ORM\Column(name="attended_at", type="datetime")
     */
    private $attendedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="created_by", type="string", length=150)
     */
    private $createdBy;

    /**
     * @var datetime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */

    private $updatedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="updated_by", type="string", length=150)
     */
    private $updatedBy;

    /**
     * @var string
     *
     * @ORM\Column(name="remarks", type="string", length=256)
     */
    private $remarks;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=3)
     * @Assert\NotBlank()
     */
    private $status;

    /**
     * Get eventDetailId
     *
     * @return integer
     */
    public function getEventDetailId()
    {
        return $this->eventDetailId;
    }

    /**
     * Set eventId
     *
     * @param integer $eventId
     *
     * @return ApEventDetail
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
     * Set proVoterId
     *
     * @param integer $proVoterId
     *
     * @return ApEventDetail
     */
    public function setProVoterId($proVoterId)
    {
        $this->proVoterId = $proVoterId;

        return $this;
    }

    /**
     * Get proVoterId
     *
     * @return integer
     */
    public function getProVoterId()
    {
        return $this->proVoterId;
    }

    /**
     * Set proIdCode
     *
     * @param string $proIdCode
     *
     * @return ApEventDetail
     */
    public function setProIdCode($proIdCode)
    {
        $this->proIdCode = $proIdCode;

        return $this;
    }

    /**
     * Get proIdCode
     *
     * @return string
     */
    public function getProIdCode()
    {
        return $this->proIdCode;
    }

    /**
     * Set hasAttended
     *
     * @param integer $hasAttended
     *
     * @return ApEventDetail
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ApEventDetail
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set attendedAt
     *
     * @param \DateTime $attendedAt
     *
     * @return ApEventDetail
     */
    public function setAttendedAt($attendedAt)
    {
        $this->attendedAt = $attendedAt;

        return $this;
    }

    /**
     * Get attendedAt
     *
     * @return \DateTime
     */
    public function getAttendedAt()
    {
        return $this->attendedAt;
    }

    /**
     * Set createdBy
     *
     * @param string $createdBy
     *
     * @return ApEventDetail
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return string
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return ApEventDetail
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set updatedBy
     *
     * @param string $updatedBy
     *
     * @return ApEventDetail
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return string
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Set remarks
     *
     * @param string $remarks
     *
     * @return ApEventDetail
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
     * @return ApEventDetail
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
