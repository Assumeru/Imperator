<?php
namespace imperator;

interface Member {
	/**
	 * @return int
	 */
	public function getId();

	/**
	 * @return string
	 */
	public function getName();

	/**
	 * @return string|false
	 */
	public function getProfileLink();
}