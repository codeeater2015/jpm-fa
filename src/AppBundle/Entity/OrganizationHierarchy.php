<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * OrganizationHierarchy
 *
 * @ORM\Table(name="tbl_organization_hierarchy")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OrganizationHierarchyRepository")
 * @UniqueEntity(fields={"proVoterId"},message="Profile already been added to the hierarchy.", errorPath="proVoterId")
 */
class OrganizationHierarchy
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
     * @ORM\Column(name="voter_name", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $voterName;

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
     * @ORM\Column(name="municipality_no", type="string", length=30)
     * @Assert\NotBlank()
     */
    private $municipalityNo;

    /**
     * @var string
     *
     * @ORM\Column(name="barangay_name", type="string", length=150)
     * @Assert\NotBlank()
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
     * @ORM\Column(name="pro_voter_id", type="integer")
     * @Assert\NotBlank()
     */
    private $proVoterId;

    /**
     * @var int
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
     * @var string
     *
     * @ORM\Column(name="parent_node", type="integer")
     */
    private $parentNode;

    /**
     * @var string
     *
     * @ORM\Column(name="voter_group", type="string", length=30)
     */
    private $voterGroup;

    /**
     * @var string
     *
     * @ORM\Column(name="contact_no", type="string", length=30)
     */
    private $contactNo;

    /**
     * @var string
     *
     * @ORM\Column(name="assigned_municipality", type="string", length=150)
     */
    private $assignedMunicipality;

    /**
     * @var string
     *
     * @ORM\Column(name="assigned_mun_no", type="string", length=30)
     * @Assert\NotBlank()
     */
    private $assignedMunNo;

    /**
     * @var string
     *
     * @ORM\Column(name="assigned_barangay", type="string", length=150)
     */
    private $assignedBarangay;

    /**
     * @var string
     *
     * @ORM\Column(name="assigned_brgy_no", type="string", length=30)
     * @Assert\NotBlank()
     */
    private $assignedBrgyNo;

    /**
     * @var string
     *
     * @ORM\Column(name="assigned_purok", type="string", length=150)
     */
    private $assignedPurok;

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
     * Set voterName
     *
     * @param string $voterName
     *
     * @return OrganizationHierarchy
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
     * Set municipalityName
     *
     * @param string $municipalityName
     *
     * @return OrganizationHierarchy
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
     * Set municipalityNo
     *
     * @param string $municipalityNo
     *
     * @return OrganizationHierarchy
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
     * Set barangayName
     *
     * @param string $barangayName
     *
     * @return OrganizationHierarchy
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
     * @return OrganizationHierarchy
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
     * Set proVoterId
     *
     * @param integer $proVoterId
     *
     * @return OrganizationHierarchy
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
     * @return OrganizationHierarchy
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
     * @return OrganizationHierarchy
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
     * Set parentNode
     *
     * @param integer $parentNode
     *
     * @return OrganizationHierarchy
     */
    public function setParentNode($parentNode)
    {
        $this->parentNode = $parentNode;

        return $this;
    }

    /**
     * Get parentNode
     *
     * @return integer
     */
    public function getParentNode()
    {
        return $this->parentNode;
    }

    /**
     * Set voterGroup
     *
     * @param string $voterGroup
     *
     * @return OrganizationHierarchy
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return OrganizationHierarchy
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
     * @return OrganizationHierarchy
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
     * @return OrganizationHierarchy
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
     * @return OrganizationHierarchy
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
     * Set assignedMunicipality
     *
     * @param string $assignedMunicipality
     *
     * @return OrganizationHierarchy
     */
    public function setAssignedMunicipality($assignedMunicipality)
    {
        $this->assignedMunicipality = $assignedMunicipality;

        return $this;
    }

    /**
     * Get assignedMunicipality
     *
     * @return string
     */
    public function getAssignedMunicipality()
    {
        return $this->assignedMunicipality;
    }

    /**
     * Set assignedMunNo
     *
     * @param string $assignedMunNo
     *
     * @return OrganizationHierarchy
     */
    public function setAssignedMunNo($assignedMunNo)
    {
        $this->assignedMunNo = $assignedMunNo;

        return $this;
    }

    /**
     * Get assignedMunNo
     *
     * @return string
     */
    public function getAssignedMunNo()
    {
        return $this->assignedMunNo;
    }

    /**
     * Set assignedBarangay
     *
     * @param string $assignedBarangay
     *
     * @return OrganizationHierarchy
     */
    public function setAssignedBarangay($assignedBarangay)
    {
        $this->assignedBarangay = $assignedBarangay;

        return $this;
    }

    /**
     * Get assignedBarangay
     *
     * @return string
     */
    public function getAssignedBarangay()
    {
        return $this->assignedBarangay;
    }

    /**
     * Set assignedBrgyNo
     *
     * @param string $assignedBrgyNo
     *
     * @return OrganizationHierarchy
     */
    public function setAssignedBrgyNo($assignedBrgyNo)
    {
        $this->assignedBrgyNo = $assignedBrgyNo;

        return $this;
    }

    /**
     * Get assignedBrgyNo
     *
     * @return string
     */
    public function getAssignedBrgyNo()
    {
        return $this->assignedBrgyNo;
    }

    /**
     * Set assignedPurok
     *
     * @param string $assignedPurok
     *
     * @return OrganizationHierarchy
     */
    public function setAssignedPurok($assignedPurok)
    {
        $this->assignedPurok = $assignedPurok;

        return $this;
    }

    /**
     * Get assignedPurok
     *
     * @return string
     */
    public function getAssignedPurok()
    {
        return $this->assignedPurok;
    }

    /**
     * Set contactNo
     *
     * @param string $contactNo
     *
     * @return OrganizationHierarchy
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
