<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * DataUpdateHeader
 *
 * @ORM\Table(name="tbl_data_update_header")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DataUpdateHeaderRepository")
 * @UniqueEntity(fields={"hdrId"},message="This id has already been created",errorPath="hdrId")
 */
class DataUpdateHeader
{
    /**
     * @var int
     *
     * @ORM\Column(name="hdr_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $hdrId;

    /**
     * @var string
     *
     * @ORM\Column(name="data_source", type="string", length=50)
     * @Assert\NotBlank()
     */
    private $dataSource;

    /**
     * @var int
     *
     * @ORM\Column(name="total_count", type="integer")
     * @Assert\NotBlank()
     */

    private $totalCount;

    /**
     * @var int
     *
     * @ORM\Column(name="total_processed", type="integer")
     */

    private $totalProcessed;

     /**
     * @var int
     *
     * @ORM\Column(name="total_imported", type="integer")
     */

    private $totalImported;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Assert\NotBlank() 
     */
    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="created_by", type="string", length=150)
     * @Assert\NotBlank()
     */
    private $createdBy;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=3)
     * @Assert\NotBlank()
     */
    private $status;

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
     * Set dataSource
     *
     * @param string $dataSource
     *
     * @return DataUpdateHeader
     */
    public function setDataSource($dataSource)
    {
        $this->dataSource = $dataSource;

        return $this;
    }

    /**
     * Get dataSource
     *
     * @return string
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }

    /**
     * Set totalCount
     *
     * @param integer $totalCount
     *
     * @return DataUpdateHeader
     */
    public function setTotalCount($totalCount)
    {
        $this->totalCount = $totalCount;

        return $this;
    }

    /**
     * Get totalCount
     *
     * @return integer
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return DataUpdateHeader
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
     * @return DataUpdateHeader
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
     * Set status
     *
     * @param string $status
     *
     * @return DataUpdateHeader
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
     * Set totalProcessed
     *
     * @param integer $totalProcessed
     *
     * @return DataUpdateHeader
     */
    public function setTotalProcessed($totalProcessed)
    {
        $this->totalProcessed = $totalProcessed;

        return $this;
    }

    /**
     * Get totalProcessed
     *
     * @return integer
     */
    public function getTotalProcessed()
    {
        return $this->totalProcessed;
    }

    /**
     * Set totalImported
     *
     * @param integer $totalImported
     *
     * @return DataUpdateHeader
     */
    public function setTotalImported($totalImported)
    {
        $this->totalImported = $totalImported;

        return $this;
    }

    /**
     * Get totalImported
     *
     * @return integer
     */
    public function getTotalImported()
    {
        return $this->totalImported;
    }
}
