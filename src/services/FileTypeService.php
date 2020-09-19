<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\services;

use Exception;
use reactivestudio\filestorage\exceptions\FileTypeServiceException;
use reactivestudio\filestorage\interfaces\FileStrategyInterface;
use reactivestudio\filestorage\interfaces\FileTypeInterface;
use reactivestudio\filestorage\models\type\GeneralType;
use reactivestudio\filestorage\models\type\ImageType;
use reactivestudio\filestorage\models\type\VideoType;
use reactivestudio\filestorage\strategies\BaseStrategy;
use reactivestudio\filestorage\strategies\ImageStrategy;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use Yii;

class FileTypeService
{
    /**
     * @var FileTypeInterface[]
     */
    private $types;

    /**
     * @var FileStrategyInterface[]
     */
    private $strategies;

    /**
     * @var FileTypeInterface
     */
    private $defaultType;

    /**
     * @var FileStrategyInterface
     */
    private $defaultStrategy;

    /**
     * @throws InvalidConfigException
     */
    public function __construct()
    {
        $this->defaultType = Yii::createObject(GeneralType::class);
        $this->defaultStrategy = Yii::createObject(BaseStrategy::class);

        $this->types = [
            ImageType::getName() => Yii::createObject(ImageType::class),
            VideoType::getName() => Yii::createObject(VideoType::class),
        ];

        $this->strategies = [
            ImageType::getName() => Yii::createObject(ImageStrategy::class),
            VideoType::getName() => $this->defaultStrategy,
        ];
    }

    /**
     * @param FileTypeInterface $type
     * @return FileStrategyInterface
     * @throws FileTypeServiceException
     */
    public function getStrategy(FileTypeInterface $type): FileStrategyInterface
    {
        try {
            $strategy = ArrayHelper::getValue($this->strategies, $type::getName(), $this->defaultStrategy);
        } catch (Exception $e) {
            throw new FileTypeServiceException("Error getting strategy: {$e->getMessage()}");
        }

        return $strategy;
    }

    /**
     * @param string $mime
     * @return FileTypeInterface
     */
    public function getType(string $mime): FileTypeInterface
    {
        if (empty($mime)) {
            return $this->defaultType;
        }

        foreach ($this->types as $type) {
            foreach ($type::getMimeSearchPatterns() as $pattern) {
                if (false !== strpos($mime, $pattern)) {
                    return $type;
                }
            }
        }

        return $this->defaultType;
    }

    /**
     * @param FileTypeInterface $type
     * @param FileStrategyInterface|null $strategy
     * @return FileTypeService
     */
    public function add(FileTypeInterface $type, ?FileStrategyInterface $strategy = null): self
    {
        $this->types[$type::getName()] = $type;
        $this->strategies[$type::getName()] = $strategy ?? $this->defaultStrategy;
        return $this;
    }

    /**
     * @param FileTypeInterface $type
     * @return FileTypeService
     */
    public function setDefaultType(FileTypeInterface $type): self
    {
        $this->defaultType = $type;
        return $this;
    }

    /**
     * @param FileStrategyInterface $strategy
     * @return FileTypeService
     */
    public function setDefaultStrategy(FileStrategyInterface $strategy): self
    {
        $this->defaultStrategy = $strategy;
        return $this;
    }
}
