<?php

use yii\db\Migration;

/**
 * Handles the creation of table `Backend`.
 */
class m170602_133905_create_backend_table extends Migration
{
    public function up() {
			$this->createTable('Backend', [
				'id' => $this->primaryKey(),
				'name' => $this->string()->notNull(),
			]);
    }

    public function down() {
			$this->dropTable('Backend');
		}
}
