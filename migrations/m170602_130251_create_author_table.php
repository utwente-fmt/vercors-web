<?php

use yii\db\Migration;

/**
 * Handles the creation of table `Author`.
 */
class m170602_130251_create_author_table extends Migration
{
    public function up() {
			$this->createTable('Author', [
				'id' => $this->primaryKey(),
				'firstname' => $this->string()->notNull(),
				'lastname' => $this->string()->notNull(),
			]);
    }

    public function down() {
			$this->dropTable('Author');
    }
}
