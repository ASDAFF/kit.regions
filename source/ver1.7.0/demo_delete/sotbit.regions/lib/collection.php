<?php
namespace Sotbit\Regions;

use \Bitrix\Main\SystemException;

class Collection
{
	/**
	 * @param $obj
	 * @param null $key
	 * @throws SystemException
	 */
	public function addItem($obj, $key = null)
	{
		if( $key == null )
		{
			$this->items[] = $obj;
		}
		else
		{
			if( isset( $this->items[$key] ) )
			{
				throw new SystemException( "Key $key already in use." );
			}
			else
			{
				$this->items[$key] = $obj;
			}
		}
	}

	/**
	 * @param $key
	 * @throws SystemException
	 */
	public function deleteItem($key)
	{
		if( isset( $this->items[$key] ) )
		{
			unset($this->items[$key]);
		}
		else
		{
			throw new SystemException( "Invalid key $key." );
		}
	}

	/**
	 * @param $key
	 * @return mixed
	 * @throws SystemException
	 */
	public function getItem($key)
	{
		if( isset( $this->items[$key] ) )
		{
			return $this->items[$key];
		}
		else
		{
			throw new SystemException( "Invalid key $key." );
		}
	}

	/**
	 * @return array
	 */
	public function keys()
	{
		return array_keys( $this->items );
	}

	/**
	 * @return int
	 */
	public function length()
	{
		return count( $this->items );
	}

	/**
	 * @param $key
	 * @return bool
	 */
	public function keyExists($key)
	{
		return isset( $this->items[$key] );
	}

	/**
	 * @return mixed
	 */
	public function getItems()
	{
		return $this->items;
	}
}
?>