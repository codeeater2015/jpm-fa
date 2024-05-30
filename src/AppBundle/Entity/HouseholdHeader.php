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
 * @UniqueEntity(fields={"householdCode"},message="This household id already exists.", errorPath="householdCode")
 * @UniqueEntity(fields={"voterName"},message="This leader name already exists.", errorPath="voterName")
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
     * @Assert\NotBlank()
     */
    private $firstname;

     /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $middlename;

     /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $lastname;

     /**
     * @var string
     *
     */
    private $extname;

    /**
     * @var string
     *
     */
    private $gender;

    /**
     * @var string
     *
     */
    private $civilStatus;

    /**
     * @var string
     *
     */
    private $bloodtype;
    
    /**
     * @var string
     *
     */
    private $occupation;
     
    /**
     * @var string
     *
     */
    private $ipGroup;

    /**
     * @var string
     *
     */
    private $dialect;

      /**
     * @var string
     *
     */
    private $religion;

     /**
     * @var string
     *
     */
    private $position;
    
      /**
     * @var string
     *
     * @Assert\Regex("/^(09)\d{9}$/")
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
     * @var int
     *
     */
    private $isBisaya;

     /**
     * @var int
     *
     */
    private $isCuyonon;

    /**
     * @var int
     *
     */
    private $isTagalog;

     /**
     * @var int
     *
     */
    private $isIlonggo;

    /**
     * @var int
     *
     */
    private $isCatholic;

    /**
     * @var int
     *
     */
    private $isInc;

    /**
     * @var int
     *
     */
    private $isIslam;

    /**
     * @var string
     *
     * @ORM\Column(name="voter_name", type="string", length=255)
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

    /**
     * Set firstname
     *
     * @param string $firstname
     *
     * @return HouseholdHeader
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set middlename
     *
     * @param string $middlename
     *
     * @return HouseholdHeader
     */
    public function setMiddlename($middlename)
    {
        $this->middlename = $middlename;

        return $this;
    }

    /**
     * Get middlename
     *
     * @return string
     */
    public function getMiddlename()
    {
        return $this->middlename;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     *
     * @return HouseholdHeader
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set extname
     *
     * @param string $extname
     *
     * @return HouseholdHeader
     */
    public function setExtname($extname)
    {
        $this->extname = $extname;

        return $this;
    }

    /**
     * Get extname
     *
     * @return string
     */
    public function getExtname()
    {
        return $this->extname;
    }

    /**
     * Set civilStatus
     *
     * @param string $civilStatus
     *
     * @return HouseholdHeader
     */
    public function setCivilStatus($civilStatus)
    {
        $this->civilStatus = $civilStatus;

        return $this;
    }

    /**
     * Get civilStatus
     *
     * @return string
     */
    public function getCivilStatus()
    {
        return $this->civilStatus;
    }

    /**
     * Set bloodtype
     *
     * @param string $bloodtype
     *
     * @return HouseholdHeader
     */
    public function setBloodtype($bloodtype)
    {
        $this->bloodtype = $bloodtype;

        return $this;
    }

    /**
     * Get bloodtype
     *
     * @return string
     */
    public function getBloodtype()
    {
        return $this->bloodtype;
    }

    /**
     * Set occupation
     *
     * @param string $occupation
     *
     * @return HouseholdHeader
     */
    public function setOccupation($occupation)
    {
        $this->occupation = $occupation;

        return $this;
    }

    /**
     * Get occupation
     *
     * @return string
     */
    public function getOccupation()
    {
        return $this->occupation;
    }

    /**
     * Set ipGroup
     *
     * @param string $ipGroup
     *
     * @return HouseholdHeader
     */
    public function setIpGroup($ipGroup)
    {
        $this->ipGroup = $ipGroup;

        return $this;
    }

    /**
     * Get ipGroup
     *
     * @return string
     */
    public function getIpGroup()
    {
        return $this->ipGroup;
    }

    /**
     * Set dialect
     *
     * @param string $dialect
     *
     * @return HouseholdHeader
     */
    public function setDialect($dialect)
    {
        $this->dialect = $dialect;

        return $this;
    }

    /**
     * Get dialect
     *
     * @return string
     */
    public function getDialect()
    {
        return $this->dialect;
    }

    /**
     * Set religion
     *
     * @param string $religion
     *
     * @return HouseholdHeader
     */
    public function setReligion($religion)
    {
        $this->religion = $religion;

        return $this;
    }

    /**
     * Get religion
     *
     * @return string
     */
    public function getReligion()
    {
        return $this->religion;
    }

    /**
     * Set cellphone
     *
     * @param string $cellphone
     *
     * @return HouseholdHeader
     */
    public function setCellphone($cellphone)
    {
        $this->cellphone = $cellphone;

        return $this;
    }

    /**
     * Get cellphone
     *
     * @return string
     */
    public function getCellphone()
    {
        return $this->cellphone;
    }

    /**
     * Set voterGroup
     *
     * @param string $voterGroup
     *
     * @return HouseholdHeader
     */
    public function setVoterGroup($voterGroup)
    {
        $this->voterGroup = $voterGroup;

        return $this;
    }

    /**
     * Get voterGroup
     *
     * @return string
     */
    public function getVoterGroup()
    {
        return $this->voterGroup;
    }

    /**
     * Set birthdate
     *
     * @param string $birthdate
     *
     * @return HouseholdHeader
     */
    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    /**
     * Get birthdate
     *
     * @return string
     */
    public function getBirthdate()
    {
        return $this->birthdate;
    }

    /**
     * Set gender
     *
     * @param string $gender
     *
     * @return HouseholdHeader
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set proIdCode
     *
     * @param string $proIdCode
     *
     * @return ProjectVoter
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
     * Set isBisaya
     *
     * @param integer $isBisaya
     *
     * @return ProjectVoter
     */
    public function setIsBisaya($isBisaya)
    {
        $this->isBisaya = $isBisaya;

        return $this;
    }

    /**
     * Get isBisaya
     *
     * @return integer
     */
    public function getIsBisaya()
    {
        return $this->isBisaya;
    }

    /**
     * Set isCuyonon
     *
     * @param integer $isCuyonon
     *
     * @return ProjectVoter
     */
    public function setIsCuyonon($isCuyonon)
    {
        $this->isCuyonon = $isCuyonon;

        return $this;
    }

    /**
     * Get isCuyonon
     *
     * @return integer
     */
    public function getIsCuyonon()
    {
        return $this->isCuyonon;
    }

    /**
     * Set isTagalog
     *
     * @param integer $isTagalog
     *
     * @return ProjectVoter
     */
    public function setIsTagalog($isTagalog)
    {
        $this->isTagalog = $isTagalog;

        return $this;
    }

    /**
     * Get isTagalog
     *
     * @return integer
     */
    public function getIsTagalog()
    {
        return $this->isTagalog;
    }

    /**
     * Set isIlonggo
     *
     * @param integer $isIlonggo
     *
     * @return ProjectVoter
     */
    public function setIsIlonggo($isIlonggo)
    {
        $this->isIlonggo = $isIlonggo;

        return $this;
    }

    /**
     * Get isIlonggo
     *
     * @return integer
     */
    public function getIsIlonggo()
    {
        return $this->isIlonggo;
    }

    /**
     * Set isCatholic
     *
     * @param integer $isCatholic
     *
     * @return ProjectVoter
     */
    public function setIsCatholic($isCatholic)
    {
        $this->isCatholic = $isCatholic;

        return $this;
    }

    /**
     * Get isCatholic
     *
     * @return integer
     */
    public function getIsCatholic()
    {
        return $this->isCatholic;
    }

    /**
     * Set isInc
     *
     * @param integer $isInc
     *
     * @return ProjectVoter
     */
    public function setIsInc($isInc)
    {
        $this->isInc = $isInc;

        return $this;
    }

    /**
     * Get isInc
     *
     * @return integer
     */
    public function getIsInc()
    {
        return $this->isInc;
    }

    /**
     * Set isIslam
     *
     * @param integer $isIslam
     *
     * @return ProjectVoter
     */
    public function setIsIslam($isIslam)
    {
        $this->isIslam = $isIslam;

        return $this;
    }

    /**
     * Get isIslam
     *
     * @return integer
     */
    public function getIsIslam()
    {
        return $this->isIslam;
    }

    /**
     * Set position
     *
     * @param string $position
     *
     * @return ProjectVoter
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
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
}
