<?php

use yii\db\Migration;

/**
 * Handles the creation of table `Feature`.
 */
class m170602_133542_create_feature_table extends Migration
{
    public function up() {
			$this->createTable('Feature', [
				'id' => $this->primaryKey(),
				'name' => $this->string()->notNull(),
			]);
    }

    public function down() {
			$this->dropTable('Feature');
    }
}
