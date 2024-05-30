<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * KfcAttendanceDetail
 *
 * @ORM\Table(name="tbl_attendance_detail")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\KfcAttendanceDetailRepository")
 * @UniqueEntity(fields={"hdrId","proVoterId"},message="Attendee already exists.", errorPath="proVoterId")
 */
class KfcAttendanceDetail
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
     * @ORM\Column(name="hdr_id", type="integer")
     * @Assert\NotBlank()
     */
    private $hdrId;

    /**
     * @var string
     *
     * @ORM\Column(name="municipality_name", type="string", length=150)
     * @Assert\NotBlank()
     */
    private $municipalityName;

    /**
     * @var string
     *
     * @ORM\Column(name="barangay_name", type="string", length=150)
     * @Assert\NotBlank()
     */
    private $barangayName;

     /**
     * @var int
     *
     * @ORM\Column(name="voter_name", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $voterName;

    /**
     * @var int
     *
     * @ORM\Column(name="is_non_voter", type="integer", scale=1)
     */
    private $isNonVoter;
    
     /**
     * @var int
     *
     * @ORM\Column(name="contact_no", type="string", length=15)
     */
    private $contactNo;

    /**
     * @var int
     *
     * @ORM\Column(name="has_profile", type="integer", scale=1)
     */
    private $hasProfile;


    /**
     * @var int
     *
     * @ORM\Column(name="has_assignment", type="integer", scale=1)
     */
    private $hasAssignment;

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
     */
    private $proIdCode;

     /**
     * @var string
     *
     * @ORM\Column(name="generated_id_no", type="string", length=150)
     */
    private $generatedIdNo;

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
     * Set hdrId
     *
     * @param integer $hdrId
     *
     * @return KfcAttendanceDetail
     */
    public function setHdrId($hdrId)
    {
        $this->hdrId = $hdrId;

        return $this;
    }

    /**
     * Get hdrId
     *
     * @return integer
     */
    public function getHdrId()
    {
        return $this->hdrId;
    }

    /**
     * Set municipalityName
     *
     * @param string $municipalityName
     *
     * @return KfcAttendanceDetail
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
     * @return KfcAttendanceDetail
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
     * Set voterName
     *
     * @param string $voterName
     *
     * @return KfcAttendanceDetail
     */
    public function setVoterName($voterName)
    {
        $this->voterName = $voterName;

        return $this;
    }

    /**
     * Get voterName
     *
     * @return string
     */
    public function getVoterName()
    {
        return $this->voterName;
    }

    /**
     * Set isNonVoter
     *
     * @param integer $isNonVoter
     *
     * @return KfcAttendanceDetail
     */
    public function setIsNonVoter($isNonVoter)
    {
        $this->isNonVoter = $isNonVoter;

        return $this;
    }

    /**
     * Get isNonVoter
     *
     * @return integer
     */
    public function getIsNonVoter()
    {
        return $this->isNonVoter;
    }

    /**
     * Set contactNo
     *
     * @param string $contactNo
     *
     * @return KfcAttendanceDetail
     */
    public function setContactNo($contactNo)
    {
        $this->contactNo = $contactNo;

        return $this;
    }

    /**
     * Get contactNo
     *
     * @return string
     */
    public function getContactNo()
    {
        return $this->contactNo;
    }

    /**
     * Set hasProfile
     *
     * @param integer $hasProfile
     *
     * @return KfcAttendanceDetail
     */
    public function setHasProfile($hasProfile)
    {
        $this->hasProfile = $hasProfile;

        return $this;
    }

    /**
     * Get hasProfile
     *
     * @return integer
     */
    public function getHasProfile()
    {
        return $this->hasProfile;
    }

    /**
     * Set hasAssignment
     *
     * @param integer $hasAssignment
     *
     * @return KfcAttendanceDetail
     */
    public function setHasAssignment($hasAssignment)
    {
        $this->hasAssignment = $hasAssignment;

        return $this;
    }

    /**
     * Get hasAssignment
     *
     * @return integer
     */
    public function getHasAssignment()
    {
        return $this->hasAssignment;
    }

    /**
     * Set proVoterId
     *
     * @param integer $proVoterId
     *
     * @return KfcAttendanceDetail
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
     * @return KfcAttendanceDetail
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
     * Set generatedIdNo
     *
     * @param string $generatedIdNo
     *
     * @return KfcAttendanceDetail
     */
    public function setGeneratedIdNo($generatedIdNo)
    {
        $this->generatedIdNo = $generatedIdNo;

        return $this;
    }

    /**
     * Get generatedIdNo
     *
     * @return string
     */
    public function getGeneratedIdNo()
    {
        return $this->generatedIdNo;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return KfcAttendanceDetail
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
     * @return KfcAttendanceDetail
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
     * @return KfcAttendanceDetail
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
     * @return KfcAttendanceDetail
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
