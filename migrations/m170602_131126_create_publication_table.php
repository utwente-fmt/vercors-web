<?php

use yii\db\Migration;

/**
 * Handles the creation of table `Publication`.
 */
class m170602_131126_create_publication_table extends Migration
{
    public function up() {
			$this->createTable('Publication', [
				'id' => $this->primaryKey(),
				'title' => $this->string()->notNull(),
				'conference' => $this->string()->notNull(),
				'year' => $this->integer(4)->notNull(),
				'url' => $this->string(512),
			]);
    }

    public function down() {
			$this->dropTable('Publication');
    }
}
