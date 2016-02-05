<?php
namespace imperator\mission;

interface Mission {
	public function getName();

	public function getId();

	public function getDescription(\imperator\Language $language);

	public function equals(Mission $that);

	public function containsEliminate();
}