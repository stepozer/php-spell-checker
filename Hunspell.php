<?php
/**
 * Hunspell spell checker api class
 */
class Hunspell
{
	protected $__options = array(
		'charset' => 'utf-8',
		'hunspellPath' => 'hunspell',
		'tmpPath' => null,
		'allowedLanguages' => array(
			'ru' => array(
				'dictionary' => 'ru_RU',
				'locale' => 'ru_RU.utf8'
			),
			'en' => array(
				'dictionary' => 'en_US',
			)
		)
	);


	/**
	 * Constructor.
	 *
	 * @param array $options - hunspell options
	 *
	 * @return void
	 */
	public function __construct($options = array())
	{
		$this->config($options);
	}


	/**
	 * Sets options array, merges passed argument with default values.
	 *
	 * @param array $options - hunspell options
	 *
	 * @return array
	 */
	public function config($options = array())
	{
		$this->__options = array_merge($this->__options, $options);

		if (!isset($this->__options['tmpPath'])) {
			$this->__options['tmpPath'] = sys_get_temp_dir();
		}

		return $this->__options;
	}


	/**
	 * Spell check for specified string.
	 *
	 * @param string $language - content language
	 * @param string $content  - content for check
	 *
	 * @return array - error words array
	 */
	public function spellCheckString($language = 'ru', $content = '')
	{
		if (!isset($this->__options['allowedLanguages'][$language])) {
			throw new HunspellException('Spell check for language: '.$language.' not supported');
		}

		$languageOptions = $this->__options['allowedLanguages'][$language];

		if (!isset($languageOptions['dictionary'])) {
			throw new HunspellException('Option "ditionary" for language: '.$language.' not specified');
		}

		if (isset($languageOptions['locale'])) {
			setlocale(LC_ALL, $languageOptions['locale']);
			putenv('LC_ALL='.$languageOptions['locale']);
		}

		$filePath = tempnam($this->__options['tmpPath'], 'spell_check_'.$language);
		file_put_contents($filePath, $content);
		exec($this->__options['hunspellPath']." -d ".$languageOptions['dictionary']." -l ".$filePath, $output);

		return $output;
	}
}

/**
 * An exception generated by Hunspell.
 */
class HunspellException extends Exception
{
}