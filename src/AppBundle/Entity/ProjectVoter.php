<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ProjectVoter
 *
 * @ORM\Table(name="tbl_project_voter")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProjectVoterRepository")
 * 
 */
class ProjectVoter
{
    /**
     * @var int
     *
     * @ORM\Column(name="pro_voter_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $proVoterId;

    /**
     * @var string
     *
     * @ORM\Column(name="voter_id", type="integer")
     */
    private $voterId;

    /**
     * @var string
     *
     * @ORM\Column(name="pro_id", type="integer")
     * @Assert\NotBlank()
     */
    private $proId;

    /**
     * @var string
     *
     * @ORM\Column(name="elect_id", type="integer")
     * @Assert\NotBlank()
     */
    private $electId;

    /**
     * @var string
     *
     * @ORM\Column(name="pro_id_code", type="string", length=30)
     */
    private $proIdCode;

    /**
     * @var string
     *
     * @ORM\Column(name="voter_name", type="string", length=256)
     * @Assert\NotBlank()
     */
    private $voterName;

    /**
     * @var string
     *
     * @ORM\Column(name="province_code", type="string", length=15)
     * @Assert\NotBlank()
     */
    private $provinceCode;

    /**
     * @var string
     *
     * @ORM\Column(name="municipality_no", type="string", length=15)
     * @Assert\NotBlank(groups={"create","edit"})
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
     * @ORM\Column(name="brgy_no", type="string", length=15)
     * @Assert\NotBlank(groups={"create","edit"})
     */
    private $brgyNo;

    /**
     * @var string
     *
     * @ORM\Column(name="barangay_name", type="string", length=255)
     */
    private $barangayName;

    /**
     * @var string
     *
     * @ORM\Column(name="asn_municipality_name", type="string", length=150)
     */
    private $asnMunicipalityName;

     /**
     * @var string
     *
     * @ORM\Column(name="asn_municipality_no", type="string", length=15)
     */
    private $asnMunicipalityNo;

    /**
     * @var string
     *
     * @ORM\Column(name="asn_barangay_name", type="string", length=150)
     */
    private $asnBarangayName;

    /**
     * @var string
     *
     * @ORM\Column(name="asn_barangay_no", type="string", length=15)
     */
    private $asnBarangayNo;

    /**
     * @var string
     *
     * @ORM\Column(name="precinct_no", type="string", length=30)
     */
    private $precinctNo;

    /**
     * @var string
     *
     * @ORM\Column(name="birthdate", type="string", length=30)
     */
    private $birthdate;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=256)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="voter_no", type="string", length=30)
     */
    private $voterNo;

    /**
     * @var string
     *
     * @ORM\Column(name="voter_group", type="string", length=30)
     */
    private $voterGroup;

    /**
     * @var string
     *
     * @ORM\Column(name="position", type="string", length=30)
     */
    private $position;

    /**
     * @var string
     *
     * @Assert\Regex("/^(09)\d{9}$/", groups={"create","edit"})
     * @ORM\Column(name="cellphone", type="string", length=30)
     */
    private $cellphone;

     /**
     * @var string
     *
     * @ORM\Column(name="old_voter_group", type="string", length=30)
     */
    private $oldVoterGroup;

    /**
     * @var int
     *
     * @ORM\Column(name="is_on_hold", type="integer")
     */
    private $isOnHold;

    /**
     * @var int
     *
     * @ORM\Column(name="has_photo", type="integer")
     */
    private $hasPhoto;

        /**
     * @var int
     *
     * @ORM\Column(name="has_attended", type="integer")
     */
    private $hasAttended;
    
    /**
     * @var int
     *
     * @ORM\Column(name="has_photo_2023", type="integer")
     */
    private $hasPhoto2023;
    

    /**
     * @var datetime
     *
     * @ORM\Column(name="photo_at", type="datetime")
     */
    private $photoAt;

    /**
     * @var int
     *
     * @ORM\Column(name="has_id", type="integer")
     */
    private $hasId;

    /**
     * @var int
     *
     * @ORM\Column(name="is_kalaban", type="integer")
     */
    private $isKalaban;

    /**
     * @var string
     *
     * @ORM\Column(name="is_kalaban_reason", type="string", length=30)
     */
    private $isKalabanReason;

    /**
     * @var string
     *
     * @ORM\Column(name="religion", type="string", length=150)
     */
    private $religion;

    /**
     * @var int
     *
     * @ORM\Column(name="did_changed", type="integer", scale=1)
     */

    private $didChanged;

      /**
     * @var int
     *
     * @ORM\Column(name="to_send", type="integer", scale=1)
     */

    private $toSend;

    /**
     * @var string
     *
     * @ORM\Column(name="generated_id_no", type="string")
     */
    private $generatedIdNo;

     /**
     * @var string
     *
     * @ORM\Column(name="date_generated", type="string", length=30)
     */
    private $dateGenerated;

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
     * @ORM\Column(name="remarks", type="string", length=256)
     */
    private $remarks;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=30)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=150)
     * @Assert\NotBlank(groups={"create","edit"})
     */
    private $firstname;

     /**
     * @var string
     *
     * @ORM\Column(name="middlename", type="string", length=150)
     * @Assert\NotBlank(groups={"create","edit"})
     */
    private $middlename;

     /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=150)
     * @Assert\NotBlank(groups={"create","edit"})
     */
    private $lastname;

     /**
     * @var string
     *
     * @ORM\Column(name="ext_name", type="string", length=150)
     */
    private $extname;

    /**
     * @var string
     *
     * @ORM\Column(name="civil_status", type="string", length=50)
     */
    private $civilStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="bloodtype", type="string", length=15)
     */
    private $bloodtype;

    /**
     * @var string
     *
     * @ORM\Column(name="occupation", type="string", length=150)
     */
    private $occupation;
     
    /**
     * @var string
     *
     * @ORM\Column(name="ip_group", type="string", length=150)
     */
    private $ipGroup;

    /**
     * @var int
     *
     * @ORM\Column(name="is_non_voter", type="integer", scale=1)
     */
    private $isNonVoter;


     /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", length=15)
     * @Assert\NotBlank(groups={"create","edit"})
     */
    private $gender;

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
     * Set voterId
     *
     * @param integer $voterId
     *
     * @return ProjectVoter
     */
    public function setVoterId($voterId)
    {
        $this->voterId = $voterId;

        return $this;
    }

    /**
     * Get voterId
     *
     * @return integer
     */
    public function getVoterId()
    {
        return $this->voterId;
    }

    /**
     * Set proId
     *
     * @param integer $proId
     *
     * @return ProjectVoter
     */
    public function setProId($proId)
    {
        $this->proId = $proId;

        return $this;
    }

    /**
     * Get proId
     *
     * @return integer
     */
    public function getProId()
    {
        return $this->proId;
    }

    /**
     * Set electId
     *
     * @param integer $electId
     *
     * @return ProjectVoter
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
     * Set voterName
     *
     * @param string $voterName
     *
     * @return ProjectVoter
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
     * Set provinceCode
     *
     * @param string $provinceCode
     *
     * @return ProjectVoter
     */
    public function setProvinceCode($provinceCode)
    {
        $this->provinceCode = $provinceCode;

        return $this;
    }

    /**
     * Get provinceCode
     *
     * @return string
     */
    public function getProvinceCode()
    {
        return $this->provinceCode;
    }

    /**
     * Set municipalityNo
     *
     * @param string $municipalityNo
     *
     * @return ProjectVoter
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
     * @return ProjectVoter
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
     * Set brgyNo
     *
     * @param string $brgyNo
     *
     * @return ProjectVoter
     */
    public function setBrgyNo($brgyNo)
    {
        $this->brgyNo = $brgyNo;

        return $this;
    }

    /**
     * Get brgyNo
     *
     * @return string
     */
    public function getBrgyNo()
    {
        return $this->brgyNo;
    }

    /**
     * Set barangayName
     *
     * @param string $barangayName
     *
     * @return ProjectVoter
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
     * Set precinctNo
     *
     * @param string $precinctNo
     *
     * @return ProjectVoter
     */
    public function setPrecinctNo($precinctNo)
    {
        $this->precinctNo = $precinctNo;

        return $this;
    }

    /**
     * Get precinctNo
     *
     * @return string
     */
    public function getPrecinctNo()
    {
        return $this->precinctNo;
    }

    /**
     * Set birthdate
     *
     * @param string $birthdate
     *
     * @return ProjectVoter
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
     * Set address
     *
     * @param string $address
     *
     * @return ProjectVoter
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set voterNo
     *
     * @param string $voterNo
     *
     * @return ProjectVoter
     */
    public function setVoterNo($voterNo)
    {
        $this->voterNo = $voterNo;

        return $this;
    }

    /**
     * Get voterNo
     *
     * @return string
     */
    public function getVoterNo()
    {
        return $this->voterNo;
    }

    /**
     * Set voterGroup
     *
     * @param string $voterGroup
     *
     * @return ProjectVoter
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
     * Set cellphone
     *
     * @param string $cellphone
     *
     * @return ProjectVoter
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
     * Set hasPhoto
     *
     * @param integer $hasPhoto
     *
     * @return ProjectVoter
     */
    public function setHasPhoto($hasPhoto)
    {
        $this->hasPhoto = $hasPhoto;

        return $this;
    }

    /**
     * Get hasPhoto
     *
     * @return integer
     */
    public function getHasPhoto()
    {
        return $this->hasPhoto;
    }

    /**
     * Set photoAt
     *
     * @param \DateTime $photoAt
     *
     * @return ProjectVoter
     */
    public function setPhotoAt($photoAt)
    {
        $this->photoAt = $photoAt;

        return $this;
    }

    /**
     * Get photoAt
     *
     * @return \DateTime
     */
    public function getPhotoAt()
    {
        return $this->photoAt;
    }

    /**
     * Set hasId
     *
     * @param integer $hasId
     *
     * @return ProjectVoter
     */
    public function setHasId($hasId)
    {
        $this->hasId = $hasId;

        return $this;
    }

    /**
     * Get hasId
     *
     * @return integer
     */
    public function getHasId()
    {
        return $this->hasId;
    }

    /**
     * Set isKalaban
     *
     * @param integer $isKalaban
     *
     * @return ProjectVoter
     */
    public function setIsKalaban($isKalaban)
    {
        $this->isKalaban = $isKalaban;

        return $this;
    }

    /**
     * Get isKalaban
     *
     * @return integer
     */
    public function getIsKalaban()
    {
        return $this->isKalaban;
    }

    /**
     * Set isKalabanReason
     *
     * @param string $isKalabanReason
     *
     * @return ProjectVoter
     */
    public function setIsKalabanReason($isKalabanReason)
    {
        $this->isKalabanReason = $isKalabanReason;

        return $this;
    }

    /**
     * Get isKalabanReason
     *
     * @return string
     */
    public function getIsKalabanReason()
    {
        return $this->isKalabanReason;
    }

    /**
     * Set religion
     *
     * @param string $religion
     *
     * @return ProjectVoter
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
     * Set didChanged
     *
     * @param integer $didChanged
     *
     * @return ProjectVoter
     */
    public function setDidChanged($didChanged)
    {
        $this->didChanged = $didChanged;

        return $this;
    }

    /**
     * Get didChanged
     *
     * @return integer
     */
    public function getDidChanged()
    {
        return $this->didChanged;
    }

    /**
     * Set toSend
     *
     * @param integer $toSend
     *
     * @return ProjectVoter
     */
    public function setToSend($toSend)
    {
        $this->toSend = $toSend;

        return $this;
    }

    /**
     * Get toSend
     *
     * @return integer
     */
    public function getToSend()
    {
        return $this->toSend;
    }

    /**
     * Set generatedIdNo
     *
     * @param string $generatedIdNo
     *
     * @return ProjectVoter
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
     * Set dateGenerated
     *
     * @param string $dateGenerated
     *
     * @return ProjectVoter
     */
    public function setDateGenerated($dateGenerated)
    {
        $this->dateGenerated = $dateGenerated;

        return $this;
    }

    /**
     * Get dateGenerated
     *
     * @return string
     */
    public function getDateGenerated()
    {
        return $this->dateGenerated;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ProjectVoter
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
     * @return ProjectVoter
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
     * @return ProjectVoter
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
     * @return ProjectVoter
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
     * @return ProjectVoter
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
     * @return ProjectVoter
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
     * Set firstname
     *
     * @param string $firstname
     *
     * @return ProjectVoter
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
     * @return ProjectVoter
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
     * @return ProjectVoter
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
     * @return ProjectVoter
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
     * @return ProjectVoter
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
     * @return ProjectVoter
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
     * @return ProjectVoter
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
     * @return ProjectVoter
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
     * Set isNonVoter
     *
     * @param integer $isNonVoter
     *
     * @return ProjectVoter
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
     * Set gender
     *
     * @param string $gender
     *
     * @return ProjectVoter
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
     * Set hasPhoto2023
     *
     * @param integer $hasPhoto2023
     *
     * @return ProjectVoter
     */
    public function setHasPhoto2023($hasPhoto2023)
    {
        $this->hasPhoto2023 = $hasPhoto2023;

        return $this;
    }

    /**
     * Get hasPhoto2023
     *
     * @return integer
     */
    public function getHasPhoto2023()
    {
        return $this->hasPhoto2023;
    }

    /**
     * Set oldVoterGroup
     *
     * @param string $oldVoterGroup
     *
     * @return ProjectVoter
     */
    public function setOldVoterGroup($oldVoterGroup)
    {
        $this->oldVoterGroup = $oldVoterGroup;

        return $this;
    }

    /**
     * Get oldVoterGroup
     *
     * @return string
     */
    public function getOldVoterGroup()
    {
        return $this->oldVoterGroup;
    }

    /**
     * Set isOnHold
     *
     * @param integer $isOnHold
     *
     * @return ProjectVoter
     */
    public function setIsOnHold($isOnHold)
    {
        $this->isOnHold = $isOnHold;

        return $this;
    }

    /**
     * Get isOnHold
     *
     * @return integer
     */
    public function getIsOnHold()
    {
        return $this->isOnHold;
    }

    /**
     * Set hasAttended
     *
     * @param integer $hasAttended
     *
     * @return ProjectVoter
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
     * Set asnMunicipalityName
     *
     * @param string $asnMunicipalityName
     *
     * @return ProjectVoter
     */
    public function setAsnMunicipalityName($asnMunicipalityName)
    {
        $this->asnMunicipalityName = $asnMunicipalityName;

        return $this;
    }

    /**
     * Get asnMunicipalityName
     *
     * @return string
     */
    public function getAsnMunicipalityName()
    {
        return $this->asnMunicipalityName;
    }

    /**
     * Set asnMunicipalityNo
     *
     * @param string $asnMunicipalityNo
     *
     * @return ProjectVoter
     */
    public function setAsnMunicipalityNo($asnMunicipalityNo)
    {
        $this->asnMunicipalityNo = $asnMunicipalityNo;

        return $this;
    }

    /**
     * Get asnMunicipalityNo
     *
     * @return string
     */
    public function getAsnMunicipalityNo()
    {
        return $this->asnMunicipalityNo;
    }

    /**
     * Set asnBarangayName
     *
     * @param string $asnBarangayName
     *
     * @return ProjectVoter
     */
    public function setAsnBarangayName($asnBarangayName)
    {
        $this->asnBarangayName = $asnBarangayName;

        return $this;
    }

    /**
     * Get asnBarangayName
     *
     * @return string
     */
    public function getAsnBarangayName()
    {
        return $this->asnBarangayName;
    }

    /**
     * Set asnBarangayNo
     *
     * @param string $asnBarangayNo
     *
     * @return ProjectVoter
     */
    public function setAsnBarangayNo($asnBarangayNo)
    {
        $this->asnBarangayNo = $asnBarangayNo;

        return $this;
    }

    /**
     * Get asnBarangayNo
     *
     * @return string
     */
    public function getAsnBarangayNo()
    {
        return $this->asnBarangayNo;
    }
}
