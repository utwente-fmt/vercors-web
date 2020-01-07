<?php

use yii\db\Migration;

class m170615_075036_add_doesverify_column_to_example extends Migration
{
    public function up() {
			$this->addColumn('Example', 'doesverify', 'TINYINT(1) NOT NULL DEFAULT 1 AFTER description');
			$this->addCommentOnColumn('Example', 'doesverify', 'A boolean value indicating whether the example program verifies or not.');
    }

    public function down() {
			$this->dropCommentFromColumn('Example', 'doesverify');
			$this->dropColumn('Example', 'doesverify');
    }
}
