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

	/**
	 * @param Member $that
	 * @return bool
	 */
	public function equals(Member $that);
}