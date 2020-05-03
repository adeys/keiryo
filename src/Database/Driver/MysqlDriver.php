<?php


namespace Keiryo\Database\Driver;


class MysqlDriver extends AbstractDriver
{

    /**
     * @var array
     */
    private $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @inheritDoc
     */
    public function connect()
    {
        try {
            $config = $this->options['url']
                ? parse_url($this->options['url'])
                : $this->options;

            $dsn = "mysql:" . sprintf(
                "host=%s;port=%s;user=%s;password=%s;dbname=%s;charset=utf8mb4",
                $config["host"],
                $config["port"],
                $config["user"],
                $config["pass"],
                $config['name'] ?? ltrim($config["path"], "/")
            );

            $this->pdo = new PDO($dsn);
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function quoteTableName(string $table): string
    {
        return $table === '*'
            ? $table
            : sprintf('`%s`', $table);
    }

    /**
     * @inheritDoc
     */
    public function quoteColumnName(string $column): string
    {
        return $column === '*'
            ? $column
            : sprintf('`%s`', $column);
    }

    /**
     * @inheritDoc
     */
    public function quoteSingle($value)
    {
        return is_int($value)
            ? $value
            : sprintf("'%s'", $value);
    }
}