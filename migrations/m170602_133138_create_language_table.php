<?php

use yii\db\Migration;

/**
 * Handles the creation of table `Language`.
 */
class m170602_133138_create_language_table extends Migration
{
    public function up() {
			$this->createTable('Language', [
				'id' => $this->primaryKey(),
				'name' => $this->string()->notNull(),
			]);
    }

    public function down() {
  		$this->dropTable('Language');
    }
}
