<?php
/**
 * Klasse Database
 *
 * Die Database-Klasse stellt eine Schnittstelle zur Verbindung mit einer MySQL-Datenbank und Ausführung von SQL-Anweisungen bereit.
 * Sie bietet Methoden zum Herstellen und Schließen der Verbindung, Ausführen von SQL-Abfragen, Vorbereiten von Anweisungen,
 * Binden von Werten, Abrufen von Ergebnissen und Behandeln von Fehlern.
 *
 * Autor:   K3nguruh <https://github.com/K3nguruh>
 * Version: 1.0.0
 * Datum:   2024-05-04 16:09
 * Lizenz:  MIT-Lizenz
 */

class Database
{
  public $hostname;
  public $database;
  public $username;
  public $password;
  public $connect;
  public $debug;

  private $dbh;
  private $stmt;

  /**
   * Stellt eine Verbindung zur MySQL-Datenbank her.
   * Konfiguriert die PDO-Attribute für Fehlerbehandlung und Zeichenkodierung.
   */
  public function connect()
  {
    $this->connect = true;

    try {
      $this->dbh = new PDO("mysql:host={$this->hostname};dbname={$this->database};charset=utf8", $this->username, $this->password);
      $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $this->dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
      $this->dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      $this->dbh->setAttribute(PDO::ATTR_PERSISTENT, true);
    } catch (PDOException $e) {
      $this->handleError($e);
    }
  }

  /**
   * Schließt die Verbindung zur Datenbank.
   */
  public function close()
  {
    $this->dbh = null;
    $this->connect = false;
  }

  /**
   * Bereitet eine SQL-Anweisung zur Ausführung vor.
   * @param string $query Die SQL-Anweisung.
   */
  public function prepare($query)
  {
    try {
      $this->stmt = $this->dbh->prepare($query);
    } catch (PDOException $e) {
      $this->handleError($e);
    }
  }

  /**
   * Bindet einen Wert an einen Parameter in der vorbereiteten SQL-Anweisung.
   * @param mixed $param Der Name des Parameters.
   * @param mixed $value Der Wert, der an den Parameter gebunden werden soll.
   * @param int $type (Optional) Der Datentyp des Parameters.
   */
  public function bindValue($param, $value, $type = null)
  {
    if (is_null($type)) {
      switch (true) {
        case is_int($value):
          $type = PDO::PARAM_INT;
          break;
        case is_bool($value):
          $type = PDO::PARAM_BOOL;
          break;
        case is_null($value):
          $type = PDO::PARAM_NULL;
          break;
        default:
          $type = PDO::PARAM_STR;
      }
    }

    try {
      $this->stmt->bindValue($param, trim($value), $type);
    } catch (PDOException $e) {
      $this->handleError($e);
    }
  }

  /**
   * Führt eine vorbereitete SQL-Anweisung aus.
   * @return bool TRUE bei Erfolg, FALSE bei Fehlern.
   */
  public function execute()
  {
    try {
      return $this->stmt->execute();
    } catch (PDOException $e) {
      $this->handleError($e);
    }
  }

  /**
   * Holt eine Zeile aus dem Ergebnis der vorbereiteten SQL-Anweisung.
   * @return mixed Ein assoziatives Array, das die nächste Zeile aus dem Ergebnisset darstellt.
   */
  public function fetch()
  {
    $this->execute();

    try {
      return $this->stmt->fetch();
    } catch (PDOException $e) {
      $this->handleError($e);
    }
  }

  /**
   * Holt alle Zeilen aus dem Ergebnis der vorbereiteten SQL-Anweisung.
   * @return array Ein zweidimensionales Array, das alle Zeilen aus dem Ergebnisset darstellt.
   */
  public function fetchAll()
  {
    $this->execute();

    try {
      return $this->stmt->fetchAll();
    } catch (PDOException $e) {
      $this->handleError($e);
    }
  }

  /**
   * Gibt die Anzahl der betroffenen Zeilen durch die vorbereitete SQL-Anweisung zurück.
   * @return int Die Anzahl der betroffenen Zeilen.
   */
  public function rowCount()
  {
    try {
      return $this->stmt->rowCount();
    } catch (PDOException $e) {
      $this->handleError($e);
    }
  }

  /**
   * Gibt die ID der zuletzt eingefügten Zeile zurück.
   * @return mixed Die ID der zuletzt eingefügten Zeile.
   */
  public function lastInsertId()
  {
    try {
      return $this->dbh->lastInsertId();
    } catch (PDOException $e) {
      $this->handleError($e);
    }
  }

  /**
   * Startet eine Transaktion.
   */
  public function beginTransaction()
  {
    try {
      return $this->dbh->beginTransaction();
    } catch (PDOException $e) {
      $this->handleError($e);
    }
  }

  /**
   * Führt eine Transaktion durch.
   */
  public function commitTransaction()
  {
    try {
      return $this->dbh->commit();
    } catch (PDOException $e) {
      $this->handleError($e);
    }
  }

  /**
   * Rollt eine Transaktion zurück.
   */
  public function rollBackTransaction()
  {
    try {
      return $this->dbh->rollBack();
    } catch (PDOException $e) {
      $this->handleError($e);
    }
  }

  /**
   * Gibt Informationen über die vorbereitete SQL-Anweisung für Debugging-Zwecke aus.
   */
  public function debugDump()
  {
    try {
      return $this->stmt->debugDumpParams();
    } catch (PDOException $e) {
      $this->handleError($e);
    }
  }

  /**
   * Behandelt Fehler, die während der Ausführung von Datenbankoperationen auftreten.
   * @param PDOException $e Die ausgelöste Exception.
   */
  private function handleError($e)
  {
    //var_dump($e);

    if ($this->debug === true) {
      $query = $e->getMessage();

      preg_match("/'([^']+)'/", $query, $matches);
      $queryPart = isset($matches[1]) ? "'" . $matches[1] . "'" : "";
      $beforePart = substr($query, 0, strpos($query, $queryPart));
      $afterPart = substr($query, strpos($query, $queryPart) + strlen($queryPart));

      die("
          <h3>Database Error:</h3>
          <div>{$beforePart}</div>
          <pre>{$queryPart}</pre>
          <div>{$afterPart}</div>
        ");
    } else {
      die("<div>Die Verbindung zur Datenbank ist zur Zeit nicht möglich!</div>");
    }
  }
}
