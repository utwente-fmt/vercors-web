<?php

use yii\db\Migration;

class m180430_114612_create_language_extension_column extends Migration
{
    public function up() {
			$this->addColumn('Language', 'extension', 'VARCHAR(16) DEFAULT NULL AFTER name');
    }

    public function down() {
			$this->dropColumn('Language', 'extension');
    }
}
