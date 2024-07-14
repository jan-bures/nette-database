<?php

declare(strict_types=1);

namespace Nette\Database\Drivers;

use Nette;

/**
 * Babelfish 4.1.0 and later database driver.
 */
class BabelfishDriver extends SqlsrvDriver
{
	#[\Override]
	public function initialize(Nette\Database\Connection $connection, array $options): void
	{
		parent::initialize($connection, $options);
		$connection->getPdo()->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_LOWER);
	}

	#[\Override]
	public function getColumns(string $table): array
	{
		$columns = [];
		foreach ($this->connection->query(<<<X
			SELECT
				c.name AS name,
				o.name AS [table],
				t.name AS type,
				UPPER(t.name) AS nativetype,
				NULL AS size,
				c.is_nullable AS nullable,
				OBJECT_DEFINITION(c.default_object_id) AS [default],
				c.is_identity AS autoincrement,
				CASE WHEN pk.column_id IS NOT NULL
					THEN 1
					ELSE 0
				END AS [primary]
			FROM
				sys.columns c
				JOIN sys.objects o ON c.object_id = o.object_id
				LEFT JOIN sys.types t ON c.user_type_id = t.user_type_id
				LEFT JOIN (
					SELECT ic.object_id, ic.column_id
					FROM sys.indexes i
					JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
					WHERE i.is_primary_key = 1
				) pk ON c.object_id = pk.object_id AND c.column_id = pk.column_id
			WHERE
				o.type IN ('U', 'V')
				AND o.name = {$this->connection->quote($table)}
			X) as $row) {
			$row = (array) $row;
			$row['vendor'] = $row;
			$row['nullable'] = (bool) $row['nullable'];
			$row['autoincrement'] = (bool) $row['autoincrement'];
			$row['primary'] = (bool) $row['primary'];

			$columns[] = $row;
		}

		return $columns;
	}
}
