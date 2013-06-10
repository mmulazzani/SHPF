<?php

namespace SHPF;

interface ICryptoProvider
{
	public function encrypt ($message);
	public function decrypt ($message);
}