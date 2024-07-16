<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Database;

use Nette;


/**
 * Represents a single table row.
 */
class Row extends Nette\Utils\ArrayHash implements IRow
{
	public function __get(mixed $key)
	{
		$key = is_string($key) ? mb_strtolower($key) : $key;

		if (property_exists($this,$key)) {
			return $this->$key;
		}

		$hint = Nette\Utils\Helpers::getSuggestion(array_map('strval', array_keys((array) $this)), $key);
		throw new Nette\MemberAccessException("Cannot read an undeclared column '$key'" . ($hint ? ", did you mean '$hint'?" : '.'));
	}


	public function __isset(string $key): bool
	{
		$key = mb_strtolower($key);
		return property_exists($this, $key);
	}


	/**
	 * Returns a item.
	 * @param  string|int  $key  key or index
	 */
	public function offsetGet($key): mixed
	{
		if (is_int($key)) {
			$arr = array_slice((array) $this, $key, 1);
			if (!$arr) {
				throw new Nette\MemberAccessException("Cannot read an undeclared column '$key'.");
			}

			return current($arr);
		}

		$key = $key !== null ? mb_strtolower($key) : null;
		return $this->$key;
	}


	/**
	 * Checks if $key exists.
	 * @param  string|int  $key  key or index
	 */
	public function offsetExists($key): bool
	{
		if (is_int($key)) {
			return (bool) current(array_slice((array) $this, $key, 1));
		}

		$key = $key !== null ? mb_strtolower($key) : null;
		return parent::offsetExists($key);
	}
}
