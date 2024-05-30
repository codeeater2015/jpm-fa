<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * KfcAttendance
 *
 * @ORM\Table(name="tbl_attendance")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\KfcAttendanceRepository")
 * @UniqueEntity(fields={"description","municipalityName", "barangayName"},message="Meeting already exists.", errorPath="description")
 */
class KfcAttendance
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
     * @ORM\Column(name="description", type="string", length=150)
     * @Assert\NotBlank()
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="municipality_name", type="string", length=150)
     */
    private $municipalityName;

     /**
     * @var string
     *
     * @ORM\Column(name="municipality_no", type="string", length=30)
     * @Assert\NotBlank()
     */
    private $municipalityNo;

    /**
     * @var string
     *
     * @ORM\Column(name="barangay_name", type="string", length=150)
     */
    private $barangayName;

    /**
     * @var string
     *
     * @ORM\Column(name="barangay_no", type="string", length=30)
     * @Assert\NotBlank()
     */
    private $barangayNo;

     /**
     * @var int
     *
     * @ORM\Column(name="meeting_date", type="string", length=15)
     * @Assert\NotBlank()
     */
    private $meetingDate;

    /**
     * @var int
     *
     * @ORM\Column(name="meeting_time", type="string", length=15)
     */
    private $meetingTime;

     /**
     * @var string
     *
     * @ORM\Column(name="meeting_group", type="string", length=30)
     */
    private $meetingGroup;

     /**
     * @var string
     *
     * @ORM\Column(name="meeting_position", type="string", length=30)
     */
    private $meetingPosition;

     /**
     * @var datetime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="created_by", type="string", length=150)
     */
    private $createdBy;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return KfcAttendance
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set municipalityName
     *
     * @param string $municipalityName
     *
     * @return KfcAttendance
     */
    public function setMunicipalityName($municipalityName)
    {
        $this->municipalityName = $municipalityName;

        return $this;
    }

    /**
     * Get municipalityName
     *
     * @return string
     */
    public function getMunicipalityName()
    {
        return $this->municipalityName;
    }

    /**
     * Set barangayName
     *
     * @param string $barangayName
     *
     * @return KfcAttendance
     */
    public function setBarangayName($barangayName)
    {
        $this->barangayName = $barangayName;

        return $this;
    }

    /**
     * Get barangayName
     *
     * @return string
     */
    public function getBarangayName()
    {
        return $this->barangayName;
    }

    /**
     * Set meetingDate
     *
     * @param string $meetingDate
     *
     * @return KfcAttendance
     */
    public function setMeetingDate($meetingDate)
    {
        $this->meetingDate = $meetingDate;

        return $this;
    }

    /**
     * Get meetingDate
     *
     * @return string
     */
    public function getMeetingDate()
    {
        return $this->meetingDate;
    }

    /**
     * Set meetingTime
     *
     * @param string $meetingTime
     *
     * @return KfcAttendance
     */
    public function setMeetingTime($meetingTime)
    {
        $this->meetingTime = $meetingTime;

        return $this;
    }

    /**
     * Get meetingTime
     *
     * @return string
     */
    public function getMeetingTime()
    {
        return $this->meetingTime;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return KfcAttendance
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
     * Set createdBy
     *
     * @param string $createdBy
     *
     * @return KfcAttendance
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
     * Set remarks
     *
     * @param string $remarks
     *
     * @return KfcAttendance
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
     * @return KfcAttendance
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
     * Set municipalityNo
     *
     * @param string $municipalityNo
     *
     * @return KfcAttendance
     */
    public function setMunicipalityNo($municipalityNo)
    {
        $this->municipalityNo = $municipalityNo;

        return $this;
    }

    /**
     * Get municipalityNo
     *
     * @return string
     */
    public function getMunicipalityNo()
    {
        return $this->municipalityNo;
    }

    /**
     * Set barangayNo
     *
     * @param string $barangayNo
     *
     * @return KfcAttendance
     */
    public function setBarangayNo($barangayNo)
    {
        $this->barangayNo = $barangayNo;

        return $this;
    }

    /**
     * Get barangayNo
     *
     * @return string
     */
    public function getBarangayNo()
    {
        return $this->barangayNo;
    }

    /**
     * Set meetingGroup
     *
     * @param string $meetingGroup
     *
     * @return KfcAttendance
     */
    public function setMeetingGroup($meetingGroup)
    {
        $this->meetingGroup = $meetingGroup;

        return $this;
    }

    /**
     * Get meetingGroup
     *
     * @return string
     */
    public function getMeetingGroup()
    {
        return $this->meetingGroup;
    }

    /**
     * Set meetingPosition
     *
     * @param string $meetingPosition
     *
     * @return KfcAttendance
     */
    public function setMeetingPosition($meetingPosition)
    {
        $this->meetingPosition = $meetingPosition;

        return $this;
    }

    /**
     * Get meetingPosition
     *
     * @return string
     */
    public function getMeetingPosition()
    {
        return $this->meetingPosition;
    }
}
