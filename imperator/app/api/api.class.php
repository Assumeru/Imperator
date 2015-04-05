<?php
namespace imperator\api;

abstract class Api {
	public static function handleRequest(Request $request) {
		if($request->isValid()) {
		}
	}
}