<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * PrelistingHeader
 *
 * @ORM\Table(name="tbl_pre_listing_hdr")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PrelistingHeaderRepository")
 * @UniqueEntity(fields={"prelistingDate","prelistingDesc"},message="This pre-listing description already exists.", errorPath="prelistingDesc")
 */
class PrelistingHeader
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
     * @ORM\Column(name="prelisting_desc", type="string", length=150)
     * @Assert\NotBlank()
     */
    private $prelistingDesc;

    /**
     * @var string
     *
     * @ORM\Column(name="prelisting_date", type="string", length=30)
     * @Assert\NotBlank()
     */
    private $prelistingDate;

     /**
     * @var int
     *
     * @ORM\Column(name="is_printed", type="integer", scale=1)
     */

     private $isPrinted;
 
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
     * Set prelistingDesc
     *
     * @param string $prelistingDesc
     *
     * @return PrelistingHeader
     */
    public function setPrelistingDesc($prelistingDesc)
    {
        $this->prelistingDesc = $prelistingDesc;

        return $this;
    }

    /**
     * Get prelistingDesc
     *
     * @return string
     */
    public function getPrelistingDesc()
    {
        return $this->prelistingDesc;
    }

    /**
     * Set prelistingDate
     *
     * @param string $prelistingDate
     *
     * @return PrelistingHeader
     */
    public function setPrelistingDate($prelistingDate)
    {
        $this->prelistingDate = $prelistingDate;

        return $this;
    }

    /**
     * Get prelistingDate
     *
     * @return string
     */
    public function getPrelistingDate()
    {
        return $this->prelistingDate;
    }

    /**
     * Set isPrinted
     *
     * @param integer $isPrinted
     *
     * @return PrelistingHeader
     */
    public function setIsPrinted($isPrinted)
    {
        $this->isPrinted = $isPrinted;

        return $this;
    }

    /**
     * Get isPrinted
     *
     * @return integer
     */
    public function getIsPrinted()
    {
        return $this->isPrinted;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return PrelistingHeader
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
     * @return PrelistingHeader
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
     * @return PrelistingHeader
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
     * @return PrelistingHeader
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
     * @return PrelistingHeader
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
     * @return PrelistingHeader
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
