<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * HouseholdHeader
 *
 * @ORM\Table(name="tbl_household_hdr")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\HouseholdHeaderRepository")
 * @UniqueEntity(fields={"voterName"},message="This leader name already exists.", errorPath="voterName")
 * @UniqueEntity(fields={"householdCode"},message="This household no already exists.", errorPath="householdCode")
 */
class HouseholdHeader
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
     * @ORM\Column(name="elect_id", type="integer")
     * @Assert\NotBlank()
     */
    private $electId;

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
     * @var int
     *
     * @ORM\Column(name="household_no", type="integer")
     * @Assert\NotBlank()
     */
    private $householdNo;

    /**
     * @var string
     *
     * @ORM\Column(name="household_code", type="string", length=30)
     * @Assert\NotBlank()
     */
    private $householdCode;

    /**
     * @var string
     *
     * @ORM\Column(name="municipality_no", type="string", length=15)
     * @Assert\NotBlank()
     */
    private $municipalityNo;

    /**
     * @var string
     *
     * @ORM\Column(name="municipality_name", type="string", length=15)
     */
    private $municipalityName;

    /**
     * @var string
     *
     * @ORM\Column(name="barangay_name", type="string", length=255)
     */
    private $barangayName;

    /**
     * @var string
     *
     * @ORM\Column(name="barangay_no", type="string", length=15)
     * @Assert\NotBlank()
     */
    private $barangayNo;

    /**
     * @var string
     *
     */
    private $cellphone;

    /**
     * @var int
     *
     * @ORM\Column(name="contact_no", type="string")
     */
    private $contactNo;

    /**
     * @var string
     *
     */
    private $voterGroup;

    /**
     * @var string
     *
     */
    private $birthdate;

    /**
     * @var string
     *
     * @ORM\Column(name="voter_name", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $voterName;

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
     * @ORM\Column(name="status", type="string", length=3)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="remarks", type="string", length=255)
     */
    private $remarks;


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
     * Set electId
     *
     * @param integer $electId
     *
     * @return HouseholdHeader
     */
    public function setElectId($electId)
    {
        $this->electId = $electId;

        return $this;
    }

    /**
     * Get electId
     *
     * @return integer
     */
    public function getElectId()
    {
        return $this->electId;
    }

    /**
     * Set proVoterId
     *
     * @param integer $proVoterId
     *
     * @return HouseholdHeader
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
     * @return HouseholdHeader
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
     * Set householdNo
     *
     * @param integer $householdNo
     *
     * @return HouseholdHeader
     */
    public function setHouseholdNo($householdNo)
    {
        $this->householdNo = $householdNo;

        return $this;
    }

    /**
     * Get householdNo
     *
     * @return integer
     */
    public function getHouseholdNo()
    {
        return $this->householdNo;
    }

    /**
     * Set householdCode
     *
     * @param string $householdCode
     *
     * @return HouseholdHeader
     */
    public function setHouseholdCode($householdCode)
    {
        $this->householdCode = $householdCode;

        return $this;
    }

    /**
     * Get householdCode
     *
     * @return string
     */
    public function getHouseholdCode()
    {
        return $this->householdCode;
    }

    /**
     * Set municipalityNo
     *
     * @param string $municipalityNo
     *
     * @return HouseholdHeader
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
     * Set municipalityName
     *
     * @param string $municipalityName
     *
     * @return HouseholdHeader
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
     * @return HouseholdHeader
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
     * Set barangayNo
     *
     * @param string $barangayNo
     *
     * @return HouseholdHeader
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
     * Set contactNo
     *
     * @param string $contactNo
     *
     * @return HouseholdHeader
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
     * Set voterName
     *
     * @param string $voterName
     *
     * @return HouseholdHeader
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return HouseholdHeader
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
     * @return HouseholdHeader
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
     * @return HouseholdHeader
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
     * @return HouseholdHeader
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
     * Set status
     *
     * @param string $status
     *
     * @return HouseholdHeader
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
     * Set remarks
     *
     * @param string $remarks
     *
     * @return HouseholdHeader
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
}
