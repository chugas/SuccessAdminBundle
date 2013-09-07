<?php

namespace Success\AdminBundle\Twig\Extension;

use Symfony\Component\Translation\TranslatorInterface;

class UtilExtension extends \Twig_Extension {

  private $container;
  private $translator;

  public function __construct($container, TranslatorInterface $translator = null) {
    $this->container = $container;
    $this->translator = $translator;
  }

  public function getTranslator() {
    return $this->translator;
  }

  public function getName() {
    return 'success_media';
  }

  public function getFilters() {
    return array(
        'fecha' => new \Twig_Filter_Method($this, 'fecha'),
        'format_text' => new \Twig_Filter_Method($this, 'formato'),
        'truncate_text' => new \Twig_Filter_Method($this, 'truncate_text'),
        'highlight_text' => new \Twig_Filter_Method($this, 'highlight_text'),
        'excerpt_text' => new \Twig_Filter_Method($this, 'excerpt_text'),
        'truncate_words' => new \Twig_Filter_Method($this, 'truncate_words'),
        'created_ago' => new \Twig_Filter_Method($this, 'createdAgo')
    );
  }
  
  
  public function getFunctions() {
    return array(
        'web_path' => new \Twig_Function_Method($this, 'path')
    );
  }

  /**
   * @param \Sonata\MediaBundle\Model\MediaInterface $media
   * @param string                                   $format
   *
   * @return string
   */
  public function path($media, $format) {
    $mediaservice = $this->container->get('sonata.media.pool');

    $provider = $mediaservice->getProvider($media->getProviderName());

    $format = $provider->getFormatName($media, $format);

    return $provider->generatePublicUrl($media, $format);
  }

  /**
   * Formatea la fecha indicada según las características del locale seleccionado.
   * Se utiliza para mostrar correctamente las fechas en el idioma de cada usuario.
   *
   * @param string $fecha        Objeto que representa la fecha original
   * @param string $formatoFecha Formato con el que se muestra la fecha
   * @param string $formatoHora  Formato con el que se muestra la hora
   * @param string $locale       El locale al que se traduce la fecha
   */
  public function fecha($fecha, $formatoFecha = 'medium', $formatoHora = 'none', $locale = null) {
    // Código copiado de
    //   https://github.com/thaberkern/symfony/blob
    //   /b679a23c331471961d9b00eb4d44f196351067c8
    //   /src/Symfony/Bridge/Twig/Extension/TranslationExtension.php
    // Formatos: http://www.php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
    $formatos = array(
        // Fecha/Hora: (no se muestra nada)
        'none' => \IntlDateFormatter::NONE,
        // Fecha: 12/13/52  Hora: 3:30pm
        'short' => \IntlDateFormatter::SHORT,
        // Fecha: Jan 12, 1952  Hora:
        'medium' => \IntlDateFormatter::MEDIUM,
        // Fecha: January 12, 1952  Hora: 3:30:32pm
        'long' => \IntlDateFormatter::LONG,
        // Fecha: Tuesday, April 12, 1952 AD  Hora: 3:30:42pm PST
        'full' => \IntlDateFormatter::FULL,
    );

    $formateador = \IntlDateFormatter::create(
                    $locale != null ? $locale : $this->getTranslator()->getLocale(), $formatos[$formatoFecha], $formatos[$formatoHora]
    );

    if ($fecha instanceof \DateTime) {
      return $formateador->format($fecha);
    } else {
      return $formateador->format(new \DateTime($fecha));
    }
  }

  public function formato($text) {
    if (preg_match('/^<p/', $text)) {
      return $text;
    } else {
      return '<p>' . $text . '</p>';
    }
  }

  /**
   * Truncates +text+ to the length of +length+ and replaces the last three characters with the +truncate_string+
   * if the +text+ is longer than +length+.
   */
  function truncate_text($text, $length = 30, $truncate_string = '...', $truncate_lastspace = false) {
    if ($text == '') {
      return '';
    }

    $mbstring = extension_loaded('mbstring');
    if ($mbstring) {
      $old_encoding = mb_internal_encoding();
      @mb_internal_encoding(mb_detect_encoding($text));
    }
    $strlen = ($mbstring) ? 'mb_strlen' : 'strlen';
    $substr = ($mbstring) ? 'mb_substr' : 'substr';

    if ($strlen($text) > $length) {
      $truncate_text = $substr($text, 0, $length - $strlen($truncate_string));
      if ($truncate_lastspace) {
        $truncate_text = preg_replace('/\s+?(\S+)?$/', '', $truncate_text);
      }
      $text = $truncate_text . $truncate_string;
    }

    if ($mbstring) {
      @mb_internal_encoding($old_encoding);
    }

    return $text;
  }

  /**
   * Highlights the +phrase+ where it is found in the +text+ by surrounding it like
   * <strong class="highlight">I'm a highlight phrase</strong>. The highlighter can be specialized by
   * passing +highlighter+ as single-quoted string with \1 where the phrase is supposed to be inserted.
   * N.B.: The +phrase+ is sanitized to include only letters, digits, and spaces before use.
   *
   * @param string $text subject input to preg_replace.
   * @param string $phrase string or array of words to highlight
   * @param string $highlighter regex replacement input to preg_replace.
   *
   * @return string
   */
  function highlight_text($text, $phrase, $highlighter = '<strong class="highlight">\\1</strong>') {
    if (empty($text)) {
      return '';
    }

    if (empty($phrase)) {
      return $text;
    }

    if (is_array($phrase) or ($phrase instanceof sfOutputEscaperArrayDecorator)) {
      foreach ($phrase as $word) {
        $pattern[] = '/(' . preg_quote($word, '/') . ')/i';
        $replacement[] = $highlighter;
      }
    } else {
      $pattern = '/(' . preg_quote($phrase, '/') . ')/i';
      $replacement = $highlighter;
    }

    return preg_replace($pattern, $replacement, $text);
  }

  /**
   * Extracts an excerpt from the +text+ surrounding the +phrase+ with a number of characters on each side determined
   * by +radius+. If the phrase isn't found, nil is returned. Ex:
   *   excerpt("hello my world", "my", 3) => "...lo my wo..."
   * If +excerpt_space+ is true the text will only be truncated on whitespace, never inbetween words.
   * This might return a smaller radius than specified.
   *   excerpt("hello my world", "my", 3, "...", true) => "... my ..."
   */
  function excerpt_text($text, $phrase, $radius = 100, $excerpt_string = '...', $excerpt_space = false) {
    if ($text == '' || $phrase == '') {
      return '';
    }

    $mbstring = extension_loaded('mbstring');
    if ($mbstring) {
      $old_encoding = mb_internal_encoding();
      @mb_internal_encoding(mb_detect_encoding($text));
    }
    $strlen = ($mbstring) ? 'mb_strlen' : 'strlen';
    $strpos = ($mbstring) ? 'mb_strpos' : 'strpos';
    $strtolower = ($mbstring) ? 'mb_strtolower' : 'strtolower';
    $substr = ($mbstring) ? 'mb_substr' : 'substr';

    $found_pos = $strpos($strtolower($text), $strtolower($phrase));
    $return_string = '';
    if ($found_pos !== false) {
      $start_pos = max($found_pos - $radius, 0);
      $end_pos = min($found_pos + $strlen($phrase) + $radius, $strlen($text));
      $excerpt = $substr($text, $start_pos, $end_pos - $start_pos);
      $prefix = ($start_pos > 0) ? $excerpt_string : '';
      $postfix = $end_pos < $strlen($text) ? $excerpt_string : '';

      if ($excerpt_space) {
        // only cut off at ends where $exceprt_string is added
        if ($prefix) {
          $excerpt = preg_replace('/^(\S+)?\s+?/', ' ', $excerpt);
        }
        if ($postfix) {
          $excerpt = preg_replace('/\s+?(\S+)?$/', ' ', $excerpt);
        }
      }

      $return_string = $prefix . $excerpt . $postfix;
    }

    if ($mbstring) {
      @mb_internal_encoding($old_encoding);
    }
    return $return_string;
  }

  /**
   * Word wrap long lines to line_width.
   */
  function wrap_text($text, $line_width = 80) {
    return preg_replace('/(.{1,' . $line_width . '})(\s+|$)/s', "\\1\n", preg_replace("/\n/", "\n\n", $text));
  }
  
  function truncate_words($string, $words = 20) {
    $text = explode(' ', $string);
    if ($words > count($text)) {
      return $string;
    }
    return preg_replace('/((\w+\W*){' . ($words - 1) . '}(\w+))(.*)/', '${1}', $string) . '...';
  }
  
  
  public function createdAgo(\DateTime $dateTime) {
    $delta = time() - $dateTime->getTimestamp();
    if ($delta < 0)
      throw new \Exception("createdAgo is unable to handle dates in the future");

    $duration = "";
    if ($delta < 60) {
      // Segundos
      $time = $delta;
      $duration = $time . " second" . (($time > 1) ? "s" : "") . " ago";
    } else if ($delta <= 3600) {
      // Minutos
      $time = floor($delta / 60);
      $duration = $time . " minute" . (($time > 1) ? "s" : "") . " ago";
    } else if ($delta <= 86400) {
      // Horas
      $time = floor($delta / 3600);
      $duration = $time . " hour" . (($time > 1) ? "s" : "") . " ago";
    } else {
      // Días
      $time = floor($delta / 86400);
      $duration = $time . " day" . (($time > 1) ? "s" : "") . " ago";
    }

    return $duration;
  }
  

}
