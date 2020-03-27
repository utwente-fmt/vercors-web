<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Example".
 *
 * @property integer $id
 * @property integer $backendid
 * @property integer $languageid
 * @property integer $publicationid
 * @property string $description
 * @property string $date
 *
 * @property Backend $backend
 * @property Language $language
 * @property Publication $publication
 * @property ExampleFeature[] $exampleFeatures
 * @property ExampleSource[] $exampleSources
 * @property Feature[] $features
 * @property Source[] $sources
 */
class Example extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Example';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['backendid', 'languageid', 'date', 'title', 'link', 'doesverify'], 'required'],
            [['backendid', 'languageid', 'publicationid', 'linesofcode', 'linesofspec'], 'integer'],
            [['computationtime'], 'double'],
            [['doesverify'], 'boolean'],
            [['description', 'title', 'link'], 'string'],
            [['backendid'], 'exist', 'skipOnError' => true, 'targetClass' => Backend::className(), 'targetAttribute' => ['backendid' => 'id']],
            [['languageid'], 'exist', 'skipOnError' => true, 'targetClass' => Language::className(), 'targetAttribute' => ['languageid' => 'id']],
            [['publicationid'], 'exist', 'skipOnError' => true, 'targetClass' => Publication::className(), 'targetAttribute' => ['publicationid' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'backendid' => 'Back-end',
            'languageid' => 'Language',
            'publicationid' => 'Article',
            'description' => 'Description',
            'link' => 'Path to example file',
            'linesofcode' => 'Lines of code',
            'linesofspec' => 'Lines of specification',
            'computationtime' => 'Computation time',
            'doesverify' => 'Successfully verified?',
            'date' => 'Date',
        ];
    }

    // the percentage of specification line count relative to the total line count
    public function specpercentage()
    {
        if ($this->linesofcode === NULL || $this->linesofspec === NULL) {
            return NULL;
        } else {
            return round(($this->linesofspec / $this->linesofcode) * 100, 2);
        }
    }

    public function linesofcodeText()
    {
        if ($this->linesofcode === NULL) {
            return 'unknown';
        } else {
            return $this->linesofcode . ' lines (comments not included)';
        }
    }

    public function linesofspecText()
    {
        if ($this->linesofspec === NULL) {
            return 'unknown';
        }

        $spectext = '';
        $spec = $this->specpercentage();

        if ($spec !== NULL) {
            $spectext = ' (' . $spec . '% of the total)';
        }

        return $this->linesofspec . ' lines' . $spectext;
    }

    public function examplecode()
    {
        $contents = file_get_contents('https://raw.githubusercontent.com/utwente-fmt/vercors/v1.2.0/examples/' . $this->link);
        return $contents === false ? '' : $contents;
    }

    public function featuresText()
    {
        $parts = [];
        foreach ($this->features as $feature) {
            $parts[] = $feature->name;
        }
        asort($parts);
        return implode(', ', $parts);
    }

    public function sourcesText()
    {
        $parts = [];
        foreach ($this->sources as $source) {
            $parts[] = $source->name;
        }
        asort($parts);
        return implode(', ', $parts);
    }

    public function computationtimeText()
    {
        if ($this->computationtime === NULL) {
            return 'unknown';
        } else {
            return $this->computationtime . ' milliseconds';
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBackend()
    {
        return $this->hasOne(Backend::className(), ['id' => 'backendid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguage()
    {
        return $this->hasOne(Language::className(), ['id' => 'languageid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPublication()
    {
        return $this->hasOne(Publication::className(), ['id' => 'publicationid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExampleFeatures()
    {
        return $this->hasMany(ExampleFeature::className(), ['exampleid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExampleSources()
    {
        return $this->hasMany(ExampleSource::className(), ['exampleid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFeatures()
    {
        return $this->hasMany(Feature::className(), ['id' => 'featureid'])->viaTable('ExampleFeature', ['exampleid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSources()
    {
        return $this->hasMany(Source::className(), ['id' => 'sourceid'])->viaTable('ExampleSource', ['exampleid' => 'id']);
    }
}
