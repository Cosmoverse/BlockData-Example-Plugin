<?php

declare(strict_types=1);

namespace cosmicpe\blockdatatest;

use cosmicpe\blockdata\BlockData;
use pocketmine\nbt\tag\CompoundTag;

class DurableBlockData implements BlockData{

	private const TAG_DURABILITY = "durability";
	private const MAX_DURABILITY = 4;

	public static function nbtDeserialize(CompoundTag $nbt) : BlockData{
		return new DurableBlockData($nbt->getByte(self::TAG_DURABILITY));
	}

	/** @var int */
	private $durability;

	public function __construct(int $durability = self::MAX_DURABILITY){
		$this->durability = $durability;
	}

	public function getDurability() : int{
		return $this->durability;
	}

	public function decreaseDurability() : bool{
		--$this->durability;
		if($this->durability <= 0){
			$this->durability = 0;
			return false;
		}
		return true;
	}

	public function nbtSerialize() : CompoundTag{
		return CompoundTag::create()
			->setByte(self::TAG_DURABILITY, $this->durability);
	}
}