<?php

use yii\db\Migration;

/**
 * Handles the creation of table `PublicationAuthor`.
 */
class m170602_131806_create_publicationauthor_table extends Migration
{
    public function up() {
			// create the table
			$this->createTable('PublicationAuthor', [
				'authorid' => $this->integer(),
				'publicationid' => $this->integer(),
				'PRIMARY KEY(authorid, publicationid)',
			]);
			
			// creates index for column `authorid`
			$this->createIndex(
				'idx-publicationauthor-authorid',
				'PublicationAuthor',
				'authorid'
			);
			
			// add foreign key for table `Author`
			$this->addForeignKey(
				'fk-publicationauthor-authorid',
				'PublicationAuthor',
				'authorid',
				'Author',
				'id',
				'CASCADE'
			);
			
			// creates index for column `publicationid`
			$this->createIndex(
				'idx-publicationauthor-publicationid',
				'PublicationAuthor',
				'publicationid'
			);
			
			// add foreign key for table `Publication`
			$this->addForeignKey(
				'fk-publicationauthor-publicationid',
				'PublicationAuthor',
				'publicationid',
				'Publication',
				'id',
				'CASCADE'
			);
    }

    public function down() {
			// drops foreign key for table `Publication`
			$this->dropForeignKey(
				'fk-publicationauthor-publicationid',
				'PublicationAuthor'
			);
			
			// drops index for column `publicationid`
			$this->dropIndex(
				'idx-publicationauthor-publicationid',
				'PublicationAuthor'
			);
			
			// drops foreign key for table `Author`
			$this->dropForeignKey(
				'fk-publicationauthor-authorid',
				'PublicationAuthor'
			);
			
			// drops index for column `authorid`
			$this->dropIndex(
				'idx-publicationauthor-authorid',
				'PublicationAuthor'
			);
							
			$this->dropTable('PublicationAuthor');
    }
}
