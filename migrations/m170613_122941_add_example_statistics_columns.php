<?php

use yii\db\Migration;

class m170613_122941_add_example_statistics_columns extends Migration
{
    public function up() {
			$this->addColumn('Example', 'linesofcode', 'INTEGER DEFAULT NULL AFTER description');
			$this->addColumn('Example', 'linesofspec', 'INTEGER DEFAULT NULL AFTER linesofcode');
			$this->addColumn('Example', 'computationtime', 'DOUBLE DEFAULT NULL AFTER linesofspec');
    }

    public function down() {
			$this->dropColumn('Example', 'computationtime');
			$this->dropColumn('Example', 'linesofspec');
			$this->dropColumn('Example', 'linesofcode');
    }
}
