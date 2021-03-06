<?php
    class Copy
    {
        private $book_id;
        private $due_date;
        private $id;

        function __construct($book_id, $due_date, $id = null)
        {
            $this->book_id = $book_id;
            $this->due_date = $due_date;
            $this->id = $id;
        }

        function getBookId()
        {
            return $this->book_id;
        }

        function getId()
        {
            return $this->id;
        }

        function setDueDate($new_due_date)
        {
            $this->due_date = (string) $new_due_date;
        }

        function getDueDate()
        {
            return $this->due_date;
        }

        function save()
        {
            $GLOBALS['DB']->exec("INSERT INTO copies (book_id, due_date) VALUES ({$this->getBookId()}, '{$this->getDueDate()}');");
            $this->id = $GLOBALS['DB']->lastInsertId();
        }

        static function getAll()
        {
            $returned_copies = $GLOBALS['DB']->query("SELECT * FROM copies;");
            $copies = array();
            foreach($returned_copies as $copy) {
                $book_id = $copy['book_id'];
                $id = $copy['id'];
                $due_date = $copy['due_date'];
                $new_copy = new Copy($book_id, $due_date, $id);
                array_push($copies, $new_copy);
            }
            return $copies;
        }

        static function deleteAll()
        {
            $GLOBALS['DB']->exec("DELETE FROM copies;");
        }

        static function find($search_id)
        {
            $found_copy = NULL;
            $copies = Copy::getAll();
            foreach($copies as $copy) {
                $copy_id = $copy->getId();
                if ($copy_id == $search_id) {
                    $found_copy = $copy;
                }
            }
            return $found_copy;
        }

        function addCheckoutCopy($patron, $due_date)
        {
            $GLOBALS['DB']->exec("INSERT INTO checkouts (copy_id, patron_id, due_date) VALUES ({$this->getId()}, {$patron->getId()}), '{$this->getDueDate()}');");
        }

        function getPatrons()
        {
            $query = $GLOBALS['DB']->query("SELECT patron_id FROM checkouts WHERE copy_id = {$this->getId()};");
            $patron_ids = $query->fetchAll(PDO::FETCH_ASSOC);
            $patrons = array();
            foreach($patron_ids as $id) {
                $patron_id = $id['patron_id'];
                $result = $GLOBALS['DB']->query("SELECT * FROM patrons WHERE id = {$patron_id};");
                $returned_patron = $result->fetchAll(PDO::FETCH_ASSOC);
                $name = $returned_patron[0]['name'];
                $id = $returned_patron[0]['id'];
                $new_patron = new Patron($name, $id);
                array_push($patrons, $new_patron);
            }
            return $patrons;
        }

        function delete()
        {
            $GLOBALS['DB']->exec("DELETE FROM copies WHERE id = {$this->getId()};");
            $GLOBALS['DB']->exec("DELETE FROM checkouts WHERE copy_id = {$this->getId()};");
        }
    }

?>
