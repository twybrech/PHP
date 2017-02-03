<?php

class Model
{
    protected $pdo;

    public function __construct(array $config)
    {
        try {
            if ($config['engine'] == 'mysql') {
                $this->pdo = new \PDO(
                    'mysql:dbname='.$config['database'].';host='.$config['host'],
                    $config['user'],
                    $config['password']
                );
                $this->pdo->exec('SET CHARSET UTF8');
            } else {
                $this->pdo = new \PDO(
                    'sqlite:'.$config['file']
                );
            }
        } catch (\PDOException $error) {
            throw new ModelException('Unable to connect to database');
        }
    }

    /**
     * Tries to execute a statement, throw an explicit exception on failure
     */
    protected function execute(\PDOStatement $query, array $variables = array())
    {
        if (!$query->execute($variables)) {
            $errors = $query->errorInfo();
            throw new ModelException($errors[2]);
        }

        return $query;
    }

    /**
     * Inserting a book in the database
     */
     public function insertBook($title, $author, $synopsis, $image, $copies)
    {
       $query = $this->pdo->prepare('INSERT INTO livres (titre, auteur, synopsis, image)
           VALUES (?, ?, ?, ?)');
       $this->execute($query, array($title, $author, $synopsis, $image));
       $select= $this->pdo->prepare('SELECT id FROM livres ORDER BY id DESC LIMIT 1;');
       $select->execute();
       while ($line = $select->fetch()) {
           $id = $line['id'];
           for($i = 0; $i < $copies; $i++){
               $select = $this->pdo->prepare('INSERT INTO exemplaires (book_id) VALUES (?)');
               $this->execute($select, array($id));
           }
       }
    }

    /**
     * Getting all the books
     */
    public function getBooks()
    {
        $query = $this->pdo->prepare('SELECT livres.* FROM livres');

        $this->execute($query);

        return $query->fetchAll();
    }

    public function getBook($bookId){
        $query = $this->pdo->prepare('SELECT livres.* FROM livres WHERE livres.id = ?');
        $this->execute($query,array($bookId));
        return $query->fetch();
    }

    public function getCopies($bookId){
        $query = $this->pdo->prepare('SELECT * FROM exemplaires WHERE book_id = ?');
        $this->execute($query,array($bookId));
        return $query->fetchAll();
    }
}
