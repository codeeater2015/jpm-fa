<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * ApCard
 *
 * @ORM\Table(name="tbl_ap_card")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ApCardRepository")
 */
class ApCard
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
     * @ORM\Column(name="qr_code_no", type="string", length=15)
     * @Assert\NotBlank()
     */
    private $qrCodeNo;

    /**
     * @var string
     *
     * @ORM\Column(name="card_no", type="string", length=15)
     * @Assert\NotBlank()
     */
    private $cardNo;

     /**
     * @var string
     *
     * @ORM\Column(name="date_generated", type="string", length=15)
     * @Assert\NotBlank()
     */
    private $dateGenerated;

    /**
     * @var string
     *
     * @ORM\Column(name="year_generated", type="string", length=15)
     * @Assert\NotBlank()
     */
    private $yearGenerated;

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
     * @Assert\NotBlank()
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
     * @ORM\Column(name="voter_name", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $voterName;

    /**
     * @var string
     *
     * @ORM\Column(name="municipality_name", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $municipalityName;

    /**
     * @var string
     *
     * @ORM\Column(name="barangay_name", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $barangayName;

    /**
     * @var string
     *
     * @ORM\Column(name="date_activated", type="string", length=15)
     * @Assert\NotBlank()
     */
    private $dateActivated;

      /**
     * @var string
     *
     * @ORM\Column(name="contact_no", type="string", length=150)
     */
    private $contactNo;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set qrCodeNo
     *
     * @param string $qrCodeNo
     *
     * @return ApCard
     */
    public function setQrCodeNo($qrCodeNo)
    {
        $this->qrCodeNo = $qrCodeNo;

        return $this;
    }

    /**
     * Get qrCodeNo
     *
     * @return string
     */
    public function getQrCodeNo()
    {
        return $this->qrCodeNo;
    }

    /**
     * Set cardNo
     *
     * @param string $cardNo
     *
     * @return ApCard
     */
    public function setCardNo($cardNo)
    {
        $this->cardNo = $cardNo;

        return $this;
    }

    /**
     * Get cardNo
     *
     * @return string
     */
    public function getCardNo()
    {
        return $this->cardNo;
    }

    /**
     * Set dateGenerated
     *
     * @param string $dateGenerated
     *
     * @return ApCard
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
     * Set yearGenerated
     *
     * @param string $yearGenerated
     *
     * @return ApCard
     */
    public function setYearGenerated($yearGenerated)
    {
        $this->yearGenerated = $yearGenerated;

        return $this;
    }

    /**
     * Get yearGenerated
     *
     * @return string
     */
    public function getYearGenerated()
    {
        return $this->yearGenerated;
    }

    /**
     * Set proVoterId
     *
     * @param integer $proVoterId
     *
     * @return ApCard
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
     * @return ApCard
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
     * @return ApCard
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
     * @return ApCard
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
     * @return ApCard
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
     * @return ApCard
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
     * Set dateActivated
     *
     * @param string $dateActivated
     *
     * @return ApCard
     */
    public function setDateActivated($dateActivated)
    {
        $this->dateActivated = $dateActivated;

        return $this;
    }

    /**
     * Get dateActivated
     *
     * @return string
     */
    public function getDateActivated()
    {
        return $this->dateActivated;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ApCard
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
     * @return ApCard
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
     * @return ApCard
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
     * Set contactNo
     *
     * @param string $contactNo
     *
     * @return ApCard
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
