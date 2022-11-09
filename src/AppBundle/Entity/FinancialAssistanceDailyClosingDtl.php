<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * FinancialAssistanceDailyClosingDtl
 *
 * @ORM\Table(name="tbl_fa_daily_closing_dtl")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FinancialAssistanceDailyClosingDtlRepository")
 * 
 */
class FinancialAssistanceDailyClosingDtl
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
     * @Assert\NotBlank()
     */
    private $hdrId;

    /**
     * @var int
     *
     * @ORM\Column(name="trn_id", type="integer")
     * @Assert\NotBlank()
     */
    private $trnId;
    
     /**
     * @var string
     *
     * @ORM\Column(name="trn_no", type="string", length=15)
     * @Assert\NotBlank()
     */
    private $trnNo;

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
     * @ORM\Column(name="remarks", type="string", length=255)
     */
    private $remarks;
    
    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=3)
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
     * @return FinancialAssistanceDailyClosingDtl
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
     * Set trnId
     *
     * @param integer $trnId
     *
     * @return FinancialAssistanceDailyClosingDtl
     */
    public function setTrnId($trnId)
    {
        $this->trnId = $trnId;

        return $this;
    }

    /**
     * Get trnId
     *
     * @return integer
     */
    public function getTrnId()
    {
        return $this->trnId;
    }

    /**
     * Set trnNo
     *
     * @param string $trnNo
     *
     * @return FinancialAssistanceDailyClosingDtl
     */
    public function setTrnNo($trnNo)
    {
        $this->trnNo = $trnNo;

        return $this;
    }

    /**
     * Get trnNo
     *
     * @return string
     */
    public function getTrnNo()
    {
        return $this->trnNo;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return FinancialAssistanceDailyClosingDtl
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
     * @return FinancialAssistanceDailyClosingDtl
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
     * @return FinancialAssistanceDailyClosingDtl
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
     * @return FinancialAssistanceDailyClosingDtl
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
