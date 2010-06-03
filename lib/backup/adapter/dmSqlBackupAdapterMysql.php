<?php

class dmSqlBackupAdapterMysql extends dmSqlBackupAdapter
{

  public function getInfos()
  {
    return array(
      'user' => $this->connection->getOption('username'),
      'pass' => $this->connection->getOption('password'),
      'host' => preg_replace('/mysql\:host=([-\.\w]+);.*/i', '$1', $this->connection->getOption('dsn')),
      'name' => preg_replace('/mysql\:host=[-\.\w]+;dbname=([-\.\w]+);.*/i', '$1', $this->connection->getOption('dsn'))
    );
  }

  public function execute($file)
  {
    $infos = $this->getInfos();

    $command = sprintf('mysqldump --opt -h "%s" -u "%s" -p"%s" "%s" > "%s"',
      $infos['host'],
      $infos['user'],
      $infos['pass'],
      $infos['name'],
      $file
    );
    
    return $this->filesystem->execute($command);
  }
}
