<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * KamadaHeader
 *
 * @ORM\Table(name="tbl_kamada_hdr")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\KamadaHeaderRepository")
 * @UniqueEntity(fields={"proVoterId"},message="This leader name already exists.", errorPath="proVoterId", em="electPrep2024")
 * @UniqueEntity(fields={"voterName"},message="This leader name already exists.", errorPath="voterName",em="electPrep2024")
 */
class KamadaHeader
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
      * @ORM\Column(name="generated_id_no", type="string", length=100)
      */
      private $generatedIdNo;
 
     /**
      * @var string
      *
      * @ORM\Column(name="voter_name", type="string", length=255)
      */
     private $voterName;

     /**
      * @var string
      *
      * @ORM\Column(name="voter_group", type="string", length=30)
      */
      private $voterGroup;
      
     /**
      * @var string
      *
      * @ORM\Column(name="assigned_purok", type="string", length=50)
      * @Assert\NotBlank()
      */
     private $assignedPurok;


    /**
      * @var string
      *
      * @Assert\Regex("/^(09)\d{9}$/")
      * @ORM\Column(name="cellphone", type="string", length=30)
      */
     private $cellphone;
 
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
      * @ORM\Column(name="municipality_name", type="string", length=150)
      */
     private $municipalityName;
 
     /**
      * @var string
      *
      * @ORM\Column(name="barangay_name", type="string", length=150)
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
      * @var int
      *
      * @ORM\Column(name="tl_pro_voter_id", type="integer")
      * @Assert\NotBlank()
      */
      private $tlProVoterId;
 
      /**
       * @var string
       *
       * @ORM\Column(name="tl_pro_id_code", type="string", length=30)
       */
      private $tlProIdCode;
 
       /**
       * @var string
       *
       * @ORM\Column(name="tl_generated_id_no", type="string", length=100)
       */
       private $tlGeneratedIdNo;
  
      /**
       * @var string
       *
       * @ORM\Column(name="tl_voter_name", type="string", length=255)
       */
      private $tlVoterName;

     /**
      * @var \datetime
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
      * @var \datetime
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
     * Set proVoterId
     *
     * @param integer $proVoterId
     *
     * @return KamadaHeader
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
     * @return KamadaHeader
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
     * @return KamadaHeader
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
     * Set voterName
     *
     * @param string $voterName
     *
     * @return KamadaHeader
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
     * Set voterGroup
     *
     * @param string $voterGroup
     *
     * @return KamadaHeader
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
     * Set cellphone
     *
     * @param string $cellphone
     *
     * @return KamadaHeader
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
     * Set municipalityNo
     *
     * @param string $municipalityNo
     *
     * @return KamadaHeader
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
     * @return KamadaHeader
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
     * @return KamadaHeader
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
     * @return KamadaHeader
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
     * Set tlProVoterId
     *
     * @param integer $tlProVoterId
     *
     * @return KamadaHeader
     */
    public function setTlProVoterId($tlProVoterId)
    {
        $this->tlProVoterId = $tlProVoterId;

        return $this;
    }

    /**
     * Get tlProVoterId
     *
     * @return integer
     */
    public function getTlProVoterId()
    {
        return $this->tlProVoterId;
    }

    /**
     * Set tlProIdCode
     *
     * @param string $tlProIdCode
     *
     * @return KamadaHeader
     */
    public function setTlProIdCode($tlProIdCode)
    {
        $this->tlProIdCode = $tlProIdCode;

        return $this;
    }

    /**
     * Get tlProIdCode
     *
     * @return string
     */
    public function getTlProIdCode()
    {
        return $this->tlProIdCode;
    }

    /**
     * Set tlGeneratedIdNo
     *
     * @param string $tlGeneratedIdNo
     *
     * @return KamadaHeader
     */
    public function setTlGeneratedIdNo($tlGeneratedIdNo)
    {
        $this->tlGeneratedIdNo = $tlGeneratedIdNo;

        return $this;
    }

    /**
     * Get tlGeneratedIdNo
     *
     * @return string
     */
    public function getTlGeneratedIdNo()
    {
        return $this->tlGeneratedIdNo;
    }

    /**
     * Set tlVoterName
     *
     * @param string $tlVoterName
     *
     * @return KamadaHeader
     */
    public function setTlVoterName($tlVoterName)
    {
        $this->tlVoterName = $tlVoterName;

        return $this;
    }

    /**
     * Get tlVoterName
     *
     * @return string
     */
    public function getTlVoterName()
    {
        return $this->tlVoterName;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return KamadaHeader
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
     * @return KamadaHeader
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
     * @return KamadaHeader
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
     * @return KamadaHeader
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
     * @return KamadaHeader
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
     * @return KamadaHeader
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
     * Set assignedPurok
     *
     * @param string $assignedPurok
     *
     * @return KamadaHeader
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
}
