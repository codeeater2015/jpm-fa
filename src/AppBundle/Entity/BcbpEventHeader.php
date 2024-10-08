<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * BcbpEventHeader
 *
 * @ORM\Table(name="tbl_bcbp_event_header")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BcbpEventHeaderRepository")
 * @UniqueEntity(fields={"eventDate","eventDescription"},message="This event already exists.", errorPath="eventDescription")
 */
class BcbpEventHeader
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
     * @var string
     *
     * @ORM\Column(name="event_description", type="string", length=150)
     * @Assert\NotBlank()
     */
    private $eventDescription;
    
    /**
     * @var string
     *
     * @ORM\Column(name="event_date", type="string", length=15)
     * @Assert\NotBlank()
     */
    private $eventDate;

    /**
     * @var string
     *
     * @ORM\Column(name="event_type", type="string", length=15)
     * @Assert\NotBlank()
     */
    private $eventType;

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
     * Set eventDescription
     *
     * @param string $eventDescription
     *
     * @return BcbpEventHeader
     */
    public function setEventDescription($eventDescription)
    {
        $this->eventDescription = $eventDescription;

        return $this;
    }

    /**
     * Get eventDescription
     *
     * @return string
     */
    public function getEventDescription()
    {
        return $this->eventDescription;
    }

    /**
     * Set eventDate
     *
     * @param string $eventDate
     *
     * @return BcbpEventHeader
     */
    public function setEventDate($eventDate)
    {
        $this->eventDate = $eventDate;

        return $this;
    }

    /**
     * Get eventDate
     *
     * @return string
     */
    public function getEventDate()
    {
        return $this->eventDate;
    }

    /**
     * Set remarks
     *
     * @param string $remarks
     *
     * @return BcbpEventHeader
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
     * @return BcbpEventHeader
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

    /**
     * Set eventType
     *
     * @param string $eventType
     *
     * @return BcbpEventHeader
     */
    public function setEventType($eventType)
    {
        $this->eventType = $eventType;

        return $this;
    }

    /**
     * Get eventType
     *
     * @return string
     */
    public function getEventType()
    {
        return $this->eventType;
    }
}
