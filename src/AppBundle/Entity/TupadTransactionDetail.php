<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * TupadTransactionDetail
 *
 * @ORM\Table(name="tbl_tupad_transaction_dtl")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TupadTransactionDetailRepository")
 */
class TupadTransactionDetail
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
     * @ORM\Column(name="hdr_id", type="integer")
     */
    private $hdrId;

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
     * @ORM\Column(name="b_birthdate", type="string", length=150)
     */
    private $bBirthdate;
    
    /**
     * @var string
     *
     * @ORM\Column(name="b_cellphone_no", type="string", length=50)
     */
    private $bCellphoneNo;

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
     * @return TupadTransactionDetail
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
     * Set proVoterId
     *
     * @param integer $proVoterId
     *
     * @return TupadTransactionDetail
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
     * @return TupadTransactionDetail
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
     * @return TupadTransactionDetail
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
     * Set bMunicipality
     *
     * @param string $bMunicipality
     *
     * @return TupadTransactionDetail
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
     * @return TupadTransactionDetail
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
     * @return TupadTransactionDetail
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
     * @return TupadTransactionDetail
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
     * @return TupadTransactionDetail
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
     * @return TupadTransactionDetail
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
     * @return TupadTransactionDetail
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
     * @return TupadTransactionDetail
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
     * Set bBirthdate
     *
     * @param string $bBirthdate
     *
     * @return TupadTransactionDetail
     */
    public function setBBirthdate($bBirthdate)
    {
        $this->bBirthdate = $bBirthdate;

        return $this;
    }

    /**
     * Get bBirthdate
     *
     * @return string
     */
    public function getBBirthdate()
    {
        return $this->bBirthdate;
    }

    /**
     * Set bCellphoneNo
     *
     * @param string $bCellphoneNo
     *
     * @return TupadTransactionDetail
     */
    public function setBCellphoneNo($bCellphoneNo)
    {
        $this->bCellphoneNo = $bCellphoneNo;

        return $this;
    }

    /**
     * Get bCellphoneNo
     *
     * @return string
     */
    public function getBCellphoneNo()
    {
        return $this->bCellphoneNo;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return TupadTransactionDetail
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
     * @return TupadTransactionDetail
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
}
