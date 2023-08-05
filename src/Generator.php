<?php

namespace curd;


use surface\Config;

abstract class Generator
{

    protected array $use = [];

    public function __construct(protected ?Config $config = null)
    {
    }


    /**
     * 返回生成模板
     *
     * @return string
     */
    abstract public function template(): string;


    /**
     * 返回当前生成器use引入的类
     *
     * @return array
     */
    public function getUse(): array
    {
        return $this->use;
    }


    /**
     *
     * @param array|class-string $use
     *
     * @return $this
     */
    public function setUse(array|string $use): static
    {
        if (is_array($use)) {
            array_map([$this, 'setUse'], $use);
        }else if (!in_array($use, $this->use)) {
            $this->use[] = $use;
        }
        return $this;
    }


}
