<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * TupadTransaction
 *
 * @ORM\Table(name="tbl_tupad_transaction")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TupadTransactionRepository")
 */
class TupadTransaction
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
     * @ORM\GeneratedValue(strategy="AUTO")
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
     * @ORM\Column(name="generated_id_no", type="string", length=50)
     */
    private $generatedIdNo;

    /**
     * @var string
     *
     * @ORM\Column(name="source_municipality", type="string", length=150)
     */
    private $sourceMunicipality;

    /**
     * @var string
     *
     * @ORM\Column(name="source_barangay", type="string", length=150)
     */
    private $sourceBarangay;

    /**
     * @var string
     *
     * @ORM\Column(name="b_municipality", type="string", length=150)
     */
    private $bMunicipality;

    /**
     * @var string
     *
     * @ORM\Column(name="b_barangay", type="string", length=150)
     */
    private $bBarangay;
     
    /**
     * @var string
     *
     * @ORM\Column(name="b_name", type="string", length=150)
     * @Assert\NotBlank()
     */
    private $bName;

     /**
     * @var string
     *
     * @ORM\Column(name="b_firstname", type="string", length=150)
     */
    private $bFirstname;

     /**
     * @var string
     *
     * @ORM\Column(name="b_middlename", type="string", length=150)
     */
    private $bMiddlename;

     /**
     * @var string
     *
     * @ORM\Column(name="b_lastname", type="string", length=150)
     */
    private $bLastname;
    
     /**
     * @var string
     *
     * @ORM\Column(name="b_extname", type="string", length=150)
     */
    private $bExtname;

    /**
     * @var int
     *
     * @ORM\Column(name="is_voter", type="integer")
     */
    private $isVoter;

    /**
     * @var string
     *
     * @ORM\Column(name="service_type", type="string", length=50)
     * @Assert\NotBlank()
     */
    private $serviceType;

    /**
     * @var string
     *
     * @ORM\Column(name="source", type="string", length=50)
     */
    private $source;

    /**
     * @var string
     *
     * @ORM\Column(name="release_date", type="string", length=50)
     */
    private $releaseDate;

      /**
     * @var string
     *
     * @ORM\Column(name="cellphone_no", type="string", length=50)
     */
    private $cellphoneNo;

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
     */
    private $status;

     /**
     * @var string
     *
     * @ORM\Column(name="b_status", type="string", length=3)
     */
    private $bStatus;

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
     * @return TupadTransaction
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
     * @return TupadTransaction
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
     * @return TupadTransaction
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
     * Set sourceMunicipality
     *
     * @param string $sourceMunicipality
     *
     * @return TupadTransaction
     */
    public function setSourceMunicipality($sourceMunicipality)
    {
        $this->sourceMunicipality = $sourceMunicipality;

        return $this;
    }

    /**
     * Get sourceMunicipality
     *
     * @return string
     */
    public function getSourceMunicipality()
    {
        return $this->sourceMunicipality;
    }

    /**
     * Set sourceBarangay
     *
     * @param string $sourceBarangay
     *
     * @return TupadTransaction
     */
    public function setSourceBarangay($sourceBarangay)
    {
        $this->sourceBarangay = $sourceBarangay;

        return $this;
    }

    /**
     * Get sourceBarangay
     *
     * @return string
     */
    public function getSourceBarangay()
    {
        return $this->sourceBarangay;
    }

    /**
     * Set bMunicipality
     *
     * @param string $bMunicipality
     *
     * @return TupadTransaction
     */
    public function setBMunicipality($bMunicipality)
    {
        $this->bMunicipality = $bMunicipality;

        return $this;
    }

    /**
     * Get bMunicipality
     *
     * @return string
     */
    public function getBMunicipality()
    {
        return $this->bMunicipality;
    }

    /**
     * Set bBarangay
     *
     * @param string $bBarangay
     *
     * @return TupadTransaction
     */
    public function setBBarangay($bBarangay)
    {
        $this->bBarangay = $bBarangay;

        return $this;
    }

    /**
     * Get bBarangay
     *
     * @return string
     */
    public function getBBarangay()
    {
        return $this->bBarangay;
    }

    /**
     * Set bName
     *
     * @param string $bName
     *
     * @return TupadTransaction
     */
    public function setBName($bName)
    {
        $this->bName = $bName;

        return $this;
    }

    /**
     * Get bName
     *
     * @return string
     */
    public function getBName()
    {
        return $this->bName;
    }

    /**
     * Set bFirstname
     *
     * @param string $bFirstname
     *
     * @return TupadTransaction
     */
    public function setBFirstname($bFirstname)
    {
        $this->bFirstname = $bFirstname;

        return $this;
    }

    /**
     * Get bFirstname
     *
     * @return string
     */
    public function getBFirstname()
    {
        return $this->bFirstname;
    }

    /**
     * Set bMiddlename
     *
     * @param string $bMiddlename
     *
     * @return TupadTransaction
     */
    public function setBMiddlename($bMiddlename)
    {
        $this->bMiddlename = $bMiddlename;

        return $this;
    }

    /**
     * Get bMiddlename
     *
     * @return string
     */
    public function getBMiddlename()
    {
        return $this->bMiddlename;
    }

    /**
     * Set bLastname
     *
     * @param string $bLastname
     *
     * @return TupadTransaction
     */
    public function setBLastname($bLastname)
    {
        $this->bLastname = $bLastname;

        return $this;
    }

    /**
     * Get bLastname
     *
     * @return string
     */
    public function getBLastname()
    {
        return $this->bLastname;
    }

    /**
     * Set bExtname
     *
     * @param string $bExtname
     *
     * @return TupadTransaction
     */
    public function setBExtname($bExtname)
    {
        $this->bExtname = $bExtname;

        return $this;
    }

    /**
     * Get bExtname
     *
     * @return string
     */
    public function getBExtname()
    {
        return $this->bExtname;
    }

    /**
     * Set isVoter
     *
     * @param integer $isVoter
     *
     * @return TupadTransaction
     */
    public function setIsVoter($isVoter)
    {
        $this->isVoter = $isVoter;

        return $this;
    }

    /**
     * Get isVoter
     *
     * @return integer
     */
    public function getIsVoter()
    {
        return $this->isVoter;
    }

    /**
     * Set serviceType
     *
     * @param string $serviceType
     *
     * @return TupadTransaction
     */
    public function setServiceType($serviceType)
    {
        $this->serviceType = $serviceType;

        return $this;
    }

    /**
     * Get serviceType
     *
     * @return string
     */
    public function getServiceType()
    {
        return $this->serviceType;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return TupadTransaction
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
     * @return TupadTransaction
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
     * @return TupadTransaction
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
     * @return TupadTransaction
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
     * Set bStatus
     *
     * @param string $bStatus
     *
     * @return TupadTransaction
     */
    public function setBStatus($bStatus)
    {
        $this->bStatus = $bStatus;

        return $this;
    }

    /**
     * Get bStatus
     *
     * @return string
     */
    public function getBStatus()
    {
        return $this->bStatus;
    }

    /**
     * Set source
     *
     * @param string $source
     *
     * @return TupadTransaction
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set releaseDate
     *
     * @param string $releaseDate
     *
     * @return TupadTransaction
     */
    public function setReleaseDate($releaseDate)
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    /**
     * Get releaseDate
     *
     * @return string
     */
    public function getReleaseDate()
    {
        return $this->releaseDate;
    }

    /**
     * Set cellphoneNo
     *
     * @param string $cellphoneNo
     *
     * @return TupadTransaction
     */
    public function setCellphoneNo($cellphoneNo)
    {
        $this->cellphoneNo = $cellphoneNo;

        return $this;
    }

    /**
     * Get cellphoneNo
     *
     * @return string
     */
    public function getCellphoneNo()
    {
        return $this->cellphoneNo;
    }
}
