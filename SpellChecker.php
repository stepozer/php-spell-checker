<?php
/**
 * Wrapper class for hunspell command line utility
 */
class SpellChecker
{
    protected $options = [
        'hunspellPath'     => 'hunspell',
        'tmpPath'          => null,
        'allowedLanguages' => [
            'ru'    => [
                'dictionary' => 'ru_RU',
                'locale'     => 'ru_RU.utf8'
            ],
            'en'    => [
                'dictionary' => 'en_US',
            ]
        ]
    ];

    const LANG_RU = 'ru';
    const LANG_EN = 'en';

    /**
     * Constructor
     *
     * @param array $options SpellChecker class options
     *
     * @return void
     */
    public function __construct($options = [])
    {
        $this->config($options);
    }

    /**
     * Sets options array, merges passed argument with default values
     *
     * @param array $options SpellChecker class options
     *
     * @return array
     */
    protected function config($options = [])
    {
        $this->options = array_merge($this->options, $options);
        if (!isset($this->options['tmpPath'])) {
            $this->options['tmpPath'] = sys_get_temp_dir();
        }
        return $this->options;
    }

    /**
     * Spell check for specified string and return error words array
     *
     * @param string $language language (ru, en)
     * @param string $content  content for check
     *
     * @return array
     */
    public function spellCheckString($language = 'ru', $content = '')
    {
        if (!is_string($content)) {
            throw new SpellCheckerException('Content must be string');
        }
		if (!is_string($language)) {
            throw new SpellCheckerException('Language must be string');
        }
        if (!isset($this->options['allowedLanguages'][$language])) {
            throw new SpellCheckerException('Spell check for language: '.$language.' not supported');
        }

        $languageOptions = $this->options['allowedLanguages'][$language];

        if (!isset($languageOptions['dictionary'])) {
            throw new SpellCheckerException('Option "ditionary" for language: '.$language.' not specified');
        }

        if (isset($languageOptions['locale'])) {
            setlocale(LC_ALL, $languageOptions['locale']);
            putenv('LC_ALL='.$languageOptions['locale']);
        }

        $filePath = tempnam($this->options['tmpPath'], 'spell_check_'.$language);
        file_put_contents($filePath, $content);
        exec($this->options['hunspellPath']." -d ".$languageOptions['dictionary']." -l ".$filePath, $output);

        return array_unique($output);
    }
}


/**
 * An exception generated by SpellChecker class
 */
class SpellCheckerException extends Exception
{
}