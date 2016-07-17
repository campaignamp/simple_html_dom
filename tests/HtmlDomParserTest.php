<?php

use voku\helper\HtmlDomParser;
use voku\helper\SimpleHtmlDom;

/**
 * Class HtmlDomParserTest
 */
class HtmlDomParserTest extends PHPUnit_Framework_TestCase
{
  /**
   * @param $filename
   *
   * @return null|string
   */
  protected function loadFixture($filename)
  {
    $path = __DIR__ . '/fixtures/' . $filename;
    if (file_exists($path)) {
      return file_get_contents($path);
    }

    return null;
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testConstructWithInvalidArgument()
  {
    new HtmlDomParser(array('foo'));
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testLoadHtmlWithInvalidArgument()
  {
    $document = new HtmlDomParser();
    $document->loadHtml(array('foo'));
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testLoadWithInvalidArgument()
  {
    $document = new HtmlDomParser();
    $document->load(array('foo'));
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testLoadHtmlFileWithInvalidArgument()
  {
    $document = new HtmlDomParser();
    $document->loadHtmlFile(array('foo'));
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testLoad_fileWithInvalidArgument()
  {
    $document = new HtmlDomParser();
    $document->load_file(array('foo'));
  }

  /**
   * @expectedException RuntimeException
   */
  public function testLoadHtmlFileWithNotExistingFile()
  {
    $document = new HtmlDomParser();
    $document->loadHtmlFile('/path/to/file');
  }

  /**
   * @expectedException RuntimeException
   */
  public function testLoadHtmlFileWithNotLoadFile()
  {
    $document = new HtmlDomParser();
    $document->loadHtmlFile('http://fobar');
  }

  /**
   * @expectedException BadMethodCallException
   */
  public function testMethodNotExist()
  {
    $document = new HtmlDomParser();
    /** @noinspection PhpUndefinedMethodInspection */
    $document->bar();
  }

  /**
   * @expectedException BadMethodCallException
   */
  public function testStaticMethodNotExist()
  {
    /** @noinspection PhpUndefinedMethodInspection */
    HtmlDomParser::bar();
  }

  public function testNotExistProperty()
  {
    $document = new HtmlDomParser();

    /** @noinspection PhpUndefinedFieldInspection */
    self::assertNull($document->foo);
  }

  public function testConstruct()
  {
    $html = '<div>foo</div>';
    $document = new HtmlDomParser($html);

    $element = new SimpleHtmlDom($document->getDocument()->documentElement);

    self::assertEquals($html, $element->outertext);
  }

  public function testLoadHtmlFile()
  {
    $file = __DIR__ . '/fixtures/test_page.html';
    $document = new HtmlDomParser();

    $document->loadHtmlFile($file);
    self::assertNotNull(count($document('div')));

    $document->load_file($file);
    self::assertNotNull(count($document('div')));

    $document = HtmlDomParser::file_get_html($file);
    self::assertNotNull(count($document('div')));
  }

  public function testLoadHtml()
  {
    $html = $this->loadFixture('test_page.html');
    $document = new HtmlDomParser();

    $document->loadHtml($html);
    self::assertNotNull(count($document('div')));

    $document->load($html);
    self::assertNotNull(count($document('div')));

    $document = HtmlDomParser::str_get_html($html);
    self::assertNotNull(count($document('div')));
  }

  public function testGetDocument()
  {
    $document = new HtmlDomParser();
    self::assertInstanceOf('DOMDocument', $document->getDocument());
  }

  /**
   * @dataProvider findTests
   *
   * @param $html
   * @param $selector
   * @param $count
   */
  public function testFind($html, $selector, $count)
  {
    $document = new HtmlDomParser($html);
    $elements = $document->find($selector);

    self::assertInstanceOf('voku\helper\SimpleHtmlDomNode', $elements);
    self::assertEquals($count, count($elements));

    foreach ($elements as $element) {
      self::assertInstanceOf('voku\helper\SimpleHtmlDom', $element);
    }

    if ($count !== 0) {
      $element = $document->find($selector, -1);
      self::assertInstanceOf('voku\helper\SimpleHtmlDom', $element);
    }
  }

  /**
   * @return array
   */
  public function findTests()
  {
    $html = $this->loadFixture('test_page.html');

    return array(
        array($html, '.fake h2', 0),
        array($html, 'article', 16),
        array($html, '.radio', 3),
        array($html, 'input.radio', 3),
        array($html, 'ul li', 35),
        array($html, 'fieldset#forms__checkbox li, fieldset#forms__radio li', 6),
        array($html, 'input[id]', 23),
        array($html, 'input[id=in]', 1),
        array($html, '#in', 1),
        array($html, '*[id]', 52),
        array($html, 'text', 462),
        array($html, 'comment', 3),
    );
  }

  public function testHtml()
  {
    $html = $this->loadFixture('test_page.html');
    $document = new HtmlDomParser($html);

    $htmlTmp = $document->html();
    self::assertTrue(is_string($htmlTmp));

    $xmlTmp = $document->xml();
    self::assertTrue(is_string($xmlTmp));

    self::assertTrue(is_string($document->outertext));
    self::assertTrue(strlen($document) > 0);

    $html = '<div>foo</div>';
    $document = new HtmlDomParser($html);

    self::assertEquals($html, $document->html());
    self::assertEquals($html, $document->outertext);
    self::assertEquals($html, $document);
  }

  public function testInnerHtml()
  {
    $html = '<div><div>foo</div></div>';
    $document = new HtmlDomParser($html);

    self::assertEquals('<div>foo</div>', $document->innerHtml());
    self::assertEquals('<div>foo</div>', $document->innerText());
    self::assertEquals('<div>foo</div>', $document->innertext);
  }

  public function testText()
  {
    $html = '<div>foo</div>';
    $document = new HtmlDomParser($html);

    self::assertEquals('foo', $document->text());
    self::assertEquals('foo', $document->plaintext);
  }

  public function testSave()
  {
    $html = $this->loadFixture('test_page.html');
    $document = new HtmlDomParser($html);

    self::assertTrue(is_string($document->save()));
  }

  public function testClear()
  {
    $document = new HtmlDomParser();

    self::assertTrue($document->clear());
  }


  public function testStrGetHtml()
  {
    $str = <<<HTML
中

<form name="form1" method="post" action="">
    <input type="checkbox" name="checkbox1" value="checkbox1" checked>abc-1<br>
    <input type="checkbox" name="checkbox2" value="checkbox2">öäü-2<br>
    <input type="checkbox" name="checkbox3" value="checkbox3" checked>中文空白-3<br>
</form>
HTML;

    $html = HtmlDomParser::str_get_html($str);
    $checkboxArray = array();
    foreach ($html->find('input[type=checkbox]') as $checkbox) {
      if ($checkbox->checked) {
        $checkboxArray[(string)$checkbox->name] = 'checked';
      } else {
        $checkboxArray[(string)$checkbox->name] = 'not checked';
      }
    }

    self::assertEquals(3, count($checkboxArray));
    self::assertEquals('checked', $checkboxArray['checkbox1']);
    self::assertEquals('not checked', $checkboxArray['checkbox2']);
    self::assertEquals('checked', $checkboxArray['checkbox3']);
  }

  public function testOutertext()
  {
    $str = <<<HTML
<form name="form1" method="post" action=""><input type="checkbox" name="checkbox1" value="checkbox1" checked>中文空白</form>
HTML;

    $html = HtmlDomParser::str_get_html($str);

    foreach ($html->find('input') as $e) {
      $e->outertext = '[INPUT]';
    }

    self::assertEquals('<form name="form1" method="post" action="">[INPUT]中文空白</form>', (string)$html);
  }

  public function testInnertext()
  {
    $str = <<<HTML
<div id="hello">Hello</div><div id="world">World</div>
HTML;

    $html = HtmlDomParser::str_get_html($str);

    $html->find('div', 1)->class = 'bar';
    $html->find('div[id=hello]', 0)->innertext = 'foo';

    self::assertEquals('<div id="hello">foo</div><div id="world" class="bar">World</div>', (string)$html);
  }

  public function testMail2()
  {
    $filename = __DIR__ . '/fixtures/test_mail.html';
    $filenameExpected = __DIR__ . '/fixtures/test_mail_expected.html';

    $html = HtmlDomParser::file_get_html($filename);
    $htmlExpected = str_replace(array("\r\n", "\r", "\n"), "\n", file_get_contents($filenameExpected));

    // object to sting
    self::assertEquals($htmlExpected, str_replace(array("\r\n", "\r", "\n"), "\n", (string)$html));

    $preHeaderContentArray = $html->find('.preheaderContent');

    self::assertEquals('padding-top:10px; padding-right:20px; padding-bottom:10px; padding-left:20px;', $preHeaderContentArray[0]->style);
    self::assertEquals('top', $preHeaderContentArray[0]->valign);
  }

  public function testMail()
  {
    $str = <<<HTML
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title></title>
</head>
<body bgcolor="#FF9900" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<center>
  <style type="text/css">
    body {
      background: #f2f2f2;
      margin: 0;
      padding: 0;
    }

    td, p, span {
      font-family: verdana, arial, sans-serif;
      font-size: 14px;
      line-height: 16px;
      color: #666;
    }

    a {
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }
  </style>
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr>
      <td bgcolor="#FF9900">
        <img src="/images/nl/transparent.gif" alt="" width="5" height="3" border="0"></td>
    </tr>
    </tbody>
  </table>
  <table width="620" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr>
      <td>
        <!-- HEADER -->
        <table width="620" border="0" cellspacing="0" cellpadding="0">
          <tbody>
          <tr>
            <td bgcolor="#ffffff">
              <table width="620" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr>
                  <td width="12">
                    <img src="/images/nl/transparent.gif" alt="" width="12" height="43" border="0">
                  </td>
                  <td width="298" align="left" valign="middle">
                    <font style="font-family:verdana,arial,sans-serif; font-size:12px; color:#666666;" face="verdana,arial,helvetica,sans-serif" size="2" color="#666666"></font>
                  </td>
                  <td width="298" align="right" valign="middle">
                    <font style="font-family:verdana,arial,helvetica,sans-serif; font-size:18px; color:#FF9900;" face="verdana,arial,helvetica,sans-serif" size="3" color="#FF9900">test</font></td>
                  <td width="12">
                    <img src="/images/nl/transparent.gif" alt="" width="12" height="43" border="0">
                  </td>
                </tr>
                </tbody>
              </table>
            </td>
          </tr>
          <tr>
            <td>
              <a href="test" target="_blank"><img src="/images/nl/default_header_visual2.jpg" width="620" alt="test" border="0"></a>
            </td>
          </tr>
          <tr>
            <td bgcolor="#FF9900">
              <table width="620" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr>
                  <td width="12">
                    <img src="/images/nl/transparent.gif" alt="" width="12" height="5" border="0"></td>
                  <td width="300" align="left">
                    <font style="font-family:verdana,arial,sans-serif; font-size:14px; line-height:16px; color:#ffffff;" face="verdana,arial,helvetica,sans-serif" size="2" color="#ffffff">


                      <b>this is a test öäü ... foobar ... <span class="utf8">דיעס איז אַ פּרובירן!</span>span></b>
test3Html.html                      <foo id="foo">bar</foo>
                      <test_>lall</test_>
                      <br/><br/>
                      <br/><br/>

                      Lorem ipsum dolor sit amet, consectetur adipisicing elit. At commodi doloribus, esse inventore ipsam itaque laboriosam molestias nesciunt nihil reiciendis rem rerum? Aliquam aperiam doloremque ea harum laborum nam neque nostrum perferendis quas reiciendis. Ab accusamus, alias facilis labore minima molestiae nihil omnis quae quidem, reiciendis sint sit velit voluptatem!

                      <br/><br/>
                      <a href="test" style="font-family:\'Century Gothic\',verdana,sans-serif; font-size:22px; line-height:24px; color:#ffffff;" target="_blank"><img src="/images/nl/button_entdecken_de.jpg" border="0"></a>
                      <br/><br/>
                      Ihr Team
                    </font></td>
                  <td width="12">
                    <img src="/images/nl/transparent.gif" alt="" width="12" height="5" border="0"></td>

                </tr>
                <tr>
                  <td colspan="3">
                    <img src="/images/nl/transparent.gif" alt="" width="5" height="30" border="0"></td>
                </tr>
                </tbody>
              </table>
            </td>
          </tr>
          <tr>
            <td align="center" valign="top">
              <img src="/images/nl/teaser_shadow.jpg" alt="" width="620" height="16" border="0"></td>
          </tr>
          </tbody>
        </table>
      </td>
    </tr>
    <tr>
      <td>
        <!-- FOOTER -->
        <table width="620" border="0" cellspacing="0" cellpadding="0">
          <tbody>
          <tr>
            <td><img src="/images/nl/transparent.gif" alt="" width="5" height="25" border="0"></td>
          </tr>
          <tr>
            <td align="center">
              <font style="font-family:\'Century Gothic\',verdana,sans-serif; font-size:11px; line-height:14px; color:#cc0000;" face="\'Century Gothic\',verdana,sans-serif" size="1" color="#cc0000">
                <a href="test" target="_blank" style="color:#666666;"><font style="font-family:\'Century Gothic\',verdana,sans-serif; font-size:11px; line-height:14px; color:#666666;" face="\'Century Gothic\',verdana,sans-serif" size="1" color="#666666">IMPRESSUM &amp; RECHTLICHES</font></a>
              </font></td>
          </tr>
          <tr>
            <td><img src="/images/nl/transparent.gif" alt="" width="5" height="10" border="0"></td>
          </tr>
          <tr>
            <td align="center" valign="top">
              <img src="/images/nl/footer_shadow.jpg" alt="" width="620" height="14" border="0"></td>
          </tr>
          <tr>
            <td><img src="/images/i/nl/transparent.gif" alt="" width="5" height="10" border="0"></td>
          </tr>
          <tr>
            <td>
              <table width="620" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr>
                  <td width="358" align="right" valign="middle">
                    <font style="font-family:\'Century Gothic\',verdana,sans-serif; font-size:11px; line-height:14px; color:#666666;" face="\'Century Gothic\',verdana,sans-serif" size="1" color="#666666">© 2015 Test AG &amp; Co. KGaA</font>
                  </td>
                  <td width="12">
                    <img src="/images/nl/transparent.gif" alt="" width="12" height="5" border="0"></td>
                  <td width="250" align="left" valign="middle">
                    <a href="test" target="_blank"><img src="/nl/footer_logo.jpg" alt="test" width="60" height="34" border="0"></a>
                  </td>
                </tr>
                </tbody>
              </table>
            </td>
          </tr>
          <tr>
            <td><img src="/images/nl/transparent.gif" alt="○●◎ earth 中文空白" width="5" height="20" border="0"></td>
          </tr>
          </tbody>
        </table>
      </td>
    </tr>
    </tbody>
  </table>
</center>

</body>
</html>
HTML;

    $htmlTmp = HtmlDomParser::str_get_html($str);
    self::assertInstanceOf('voku\helper\HtmlDomParser', $htmlTmp);

    // replace all images with "foobar"
    $tmpArray = array();
    foreach ($htmlTmp->find('img') as $e) {
      if ($e->src != '') {
        $tmpArray[] = $e->src;

        $e->src = 'foobar';
      }
    }

    $testString = false;
    $tmpCounter = 0;
    foreach ($htmlTmp->find('table tr td img') as $e) {
      if ($e->alt == '○●◎ earth 中文空白') {
        $testString = $e->alt;
        break;
      }
      $tmpCounter++;
    }
    self::assertEquals(15, $tmpCounter);
    self::assertEquals('○●◎ earth 中文空白', $testString);

    // get the content from the css-selector

    $testStringUtf8_v1 = $htmlTmp->find('html .utf8');
    self::assertEquals('דיעס איז אַ פּרובירן!', $testStringUtf8_v1[0]->innertext);
    self::assertEquals('<span class="utf8">דיעס איז אַ פּרובירן!</span>', $testStringUtf8_v1[0]->outertext);

    $testStringUtf8_v2 = $htmlTmp->find('span.utf8');
    self::assertEquals('דיעס איז אַ פּרובירן!', $testStringUtf8_v2[0]->innertext);
    self::assertEquals('<span class="utf8">דיעס איז אַ פּרובירן!</span>', $testStringUtf8_v2[0]->outertext);

    $testStringUtf8_v3 = $htmlTmp->find('.utf8');
    self::assertEquals('דיעס איז אַ פּרובירן!', $testStringUtf8_v3[0]->innertext);
    self::assertEquals('<span class="utf8">דיעס איז אַ פּרובירן!</span>', $testStringUtf8_v3[0]->outertext);

    $testStringUtf8_v4 = $htmlTmp->find('foo');
    self::assertEquals('bar', $testStringUtf8_v4[0]->innertext);
    self::assertEquals('<foo id="foo">bar</foo>', $testStringUtf8_v4[0]->outertext);

    $testStringUtf8_v5 = $htmlTmp->find('#foo');
    self::assertEquals('bar', $testStringUtf8_v5[0]->innertext);
    self::assertEquals('<foo id="foo">bar</foo>', $testStringUtf8_v5[0]->outertext);

    $testStringUtf8_v6 = $htmlTmp->find('test_');
    self::assertEquals('lall', $testStringUtf8_v6[0]->innertext);
    self::assertEquals('<test_>lall</test_>', $testStringUtf8_v6[0]->outertext);

    $testStringUtf8_v7 = $htmlTmp->getElementById('foo');
    self::assertEquals('bar', $testStringUtf8_v7->innertext);

    $testStringUtf8_v8 = $htmlTmp->getElementByTagName('foo');
    self::assertEquals('bar', $testStringUtf8_v8->innertext);

    $testStringUtf8_v9 = $htmlTmp->getElementsByTagName('img', 15);
    self::assertEquals('○●◎ earth 中文空白', $testStringUtf8_v9->alt);
    self::assertEquals('', $testStringUtf8_v9->innertext);
    self::assertEquals('<img src="foobar" alt="○●◎ earth 中文空白" width="5" height="20" border="0">', $testStringUtf8_v9->outertext);

    // test toString
    $htmlTmp = (string)$htmlTmp;
    self::assertEquals(16, count($tmpArray));
    self::assertContains('<img src="foobar" alt="" width="5" height="3" border="0">', $htmlTmp);
    self::assertContains('© 2015 Test', $htmlTmp);
  }

  public function testEditLinks()
  {
    $texts = array(
        '<a href="http://foobar.de" class="  more  "  >Mehr</a><a href="http://foobar.de" class="  more  "  >Mehr</a>' => '<a onclick="$.get(\'/incext.php?brandcontact=1&click=1&page_id=1&brand=foobar&domain=foobar.de\');" href="http://foobar.de" data-url-parse="done" class="  more  ">Mehr</a><a onclick="$.get(\'/incext.php?brandcontact=1&click=1&page_id=1&brand=foobar&domain=foobar.de\');" href="http://foobar.de" data-url-parse="done" class="  more  ">Mehr</a>',
        ' <p><a href="http://foobar.de" class="  more  "  >Mehr</a></p>' => '<p><a onclick="$.get(\'/incext.php?brandcontact=1&click=1&page_id=1&brand=foobar&domain=foobar.de\');" href="http://foobar.de" data-url-parse="done" class="  more  ">Mehr</a></p>',
        '<a <a href="http://foobar.de">foo</a><div></div>' => '<a onclick="$.get(\'/incext.php?brandcontact=1&click=1&page_id=1&brand=foobar&domain=foobar.de\');" href="http://foobar.de" data-url-parse="done">foo</a><div></div>',
        ' <p></p>' => '<p></p>',
        ' <p>' => '<p></p>',
        'p>' => 'p>',
        'p' => 'p',
        'Google+ && Twitter || Lînux' => 'Google+ && Twitter || Lînux',
        '<a href="http://foobar.de[[foo]]&{{foobar}}&lall=1">foo</a>' => '<a onclick="$.get(\'/incext.php?brandcontact=1&click=1&page_id=1&brand=foobar&domain=foobar.de%5B%5Bfoo%5D%5D%26%7B%7Bfoobar%7D%7D%26lall%3D1\');" href="http://foobar.de[[foo]]&{{foobar}}&lall=1" data-url-parse="done">foo</a>',
        '' => '',
        '<a href=""><span>lalll=###test###&bar=%5B%5Bfoobar%5D%5D&test=[[foobar]]&foo={{lall}}</span><img src="http://foobar?lalll=###test###&bar=%5B%5Bfoobar%5D%5D&test=[[foobar]]&foo={{lall}}" style="max-width:600px;" alt="Ihr Unternehmen in den wichtigsten Online-Verzeichnissen" class="headerImage" mc:label="header_image" mc:edit="header_image" mc:allowdesigner mc:allowtext /></a>' => '<a onclick="$.get(\'/incext.php?brandcontact=1&click=1&page_id=1&brand=foobar&domain=\');" href="" data-url-parse="done"><span>lalll=###test###&bar=%5B%5Bfoobar%5D%5D&test=[[foobar]]&foo={{lall}}</span><img src="http://foobar?lalll=###test###&bar=%5B%5Bfoobar%5D%5D&test=[[foobar]]&foo={{lall}}" style="max-width:600px;" alt="Ihr Unternehmen in den wichtigsten Online-Verzeichnissen" class="headerImage" mc:label="header_image" mc:edit="header_image" mc:allowdesigner mc:allowtext></a>',
    );

    foreach($texts as $text => $expected) {
      $dom = HtmlDomParser::str_get_html($text);

      foreach ($dom->find('a') as $item) {
        $href = $item->getAttribute('href');
        $dataUrlParse = $item->getAttribute('data-url-parse');

        if ($dataUrlParse) {
          continue;
        }

        $parseLink = parse_url($href);
        $domain = (isset($parseLink['host']) ? $parseLink['host'] : '');

        $item->outertext = str_ireplace('href="' . $href . '"', ' onclick="$.get(\'/incext.php?brandcontact=1&click=1&page_id=1&brand=foobar&domain=' . urlencode($domain) . '\');" href="' . $href . '" data-url-parse="done"', $item);
      }

      self::assertEquals($expected, (string)$dom, 'tested: ' . $text);
    }
  }

  public function testEditInnerText()
  {
    $str = <<<HTML
<div id="hello">Hello</div><div id="world">World</div>
HTML;

    $html = HtmlDomParser::str_get_html($str);

    $html->find('div', 1)->class = 'bar';
    $html->find('div[id=hello]', 0)->innertext = 'foo';

    self::assertEquals('<div id="hello">foo</div><div id="world" class="bar">World</div>', (string)$html);

    // -------------

    $html->find('div[id=fail]', 0)->innertext = 'foobar';

    self::assertEquals('<div id="hello">foo</div><div id="world" class="bar">World</div>', (string)$html);
  }

  public function testLoad()
  {
    $dom = new HtmlDomParser();
    $dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
    $div = $dom->find('div', 0);
    self::assertEquals(
        '<div class="all"><p>Hey bro, <a href="google.com">click here</a><br> :)</p></div>',
        $div->outertext
    );
  }

  public function testNotLoaded()
  {
    $dom = new HtmlDomParser();
    $div = $dom->find('div', 0);

    self::assertEquals('', $div->plaintext);
  }

  public function testIncorrectAccess()
  {
    $dom = new HtmlDomParser();
    $dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
    $div = $dom->find('div', 0);
    self::assertEquals(null, $div->foo);
  }

  public function testLoadSelfclosingAttr()
  {
    $dom = new HtmlDomParser();
    $dom->load("<div class='all'><br  foo  bar  />baz</div>");
    $br = $dom->find('br', 0);
    self::assertEquals('<br foo bar>', $br->outerHtml);
  }

  public function testLoadSelfclosingAttrToString()
  {
    $dom = new HtmlDomParser();
    $dom->load("<div class='all'><br  foo  bar  />baz</div>");
    $br = $dom->find('br', 0);
    self::assertEquals('<br foo bar>', (string) $br);
  }

  public function testLoadNoOpeningTag()
  {
    $dom = new HtmlDomParser();
    $dom->load('<div class="all"><font color="red"><strong>PR Manager</strong></font></b><div class="content">content</div></div>');
    self::assertEquals('content', $dom->find('.content', 0)->text);
  }

  public function testLoadNoClosingTag()
  {
    $dom = new HtmlDomParser();
    $dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a></div>');
    $root = $dom->find('div', 0);
    self::assertEquals('<div class="all"><p>Hey bro, <a href="google.com">click here</a></p></div>', $root->outerHtml);
  }

  public function testLoadAttributeOnSelfClosing()
  {
    $dom = new HtmlDomParser();
    $dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a></div><br class="both" />');
    $br = $dom->find('br', 0);
    self::assertEquals('both', $br->getAttribute('class'));
  }

  public function testLoadClosingTagOnSelfClosing()
  {
    $dom = new HtmlDomParser();
    $dom->load('<div class="all"><br><p>Hey bro, <a href="google.com">click here</a></br></div>');
    self::assertEquals('<br><p>Hey bro, <a href="google.com">click here</a></p>', $dom->find('div', 0)->innerHtml);
  }

  public function testLoadNoValueAttribute()
  {
    $dom = new HtmlDomParser();
    $dom->load('<div class="content"><div class="grid-container" ui-view>Main content here</div></div>');
    self::assertEquals('<div class="content"><div class="grid-container" ui-view>Main content here</div></div>', $dom->innerHtml);
  }

  public function testLoadNoValueAttributeBefore()
  {
    $dom = new HtmlDomParser();
    $dom->load('<div class="content"><div ui-view class="grid-container">Main content here</div></div>');
    self::assertEquals('<div class="content"><div ui-view class="grid-container">Main content here</div></div>', $dom->innerHtml);
  }

  public function testLoadUpperCase()
  {
    $dom = new HtmlDomParser();
    $dom->load('<DIV CLASS="ALL"><BR><P>hEY BRO, <A HREF="GOOGLE.COM">click here</A></BR></DIV>');
    self::assertEquals('<br><p>hEY BRO, <a href="GOOGLE.COM">click here</a></p>', $dom->find('div', 0)->innerHtml);
  }

  public function testLoadWithFile()
  {
    $dom = new HtmlDomParser();
    $dom->load_file(__DIR__ . '/fixtures/small.html');
    self::assertEquals('VonBurgermeister', $dom->find('.post-user font', 0)->text);
  }

  public function testLoadFromFile()
  {
    $dom = new HtmlDomParser();
    $dom->load_file(__DIR__ . '/fixtures/small.html');
    self::assertEquals('VonBurgermeister', $dom->find('.post-user font', 0)->text);
  }

  public function testLoadFromFileFind()
  {
    $dom = new HtmlDomParser();
    $dom->load_file(__DIR__ . '/fixtures/small.html');
    self::assertEquals('VonBurgermeister', $dom->find('.post-row div .post-user font', 0)->text);
  }

  public function testLoadUtf8()
  {
    $dom = new HtmlDomParser();
    $dom->load('<p>Dzień</p>');
    self::assertEquals('Dzień', $dom->find('p', 0)->text);
  }

  public function testLoadFileBigTwice()
  {
    $dom = new HtmlDomParser();
    $dom->loadHtmlFile(__DIR__ . '/fixtures/big.html');
    $post = $dom->find('.post-row', 0);
    self::assertEquals('<p>Журчанье воды<br>' . "\n" . 'Черно-белые тени<br>' . "\n" . 'Вновь на фонтане</p>', $post->find('.post-message', 0)->innerHtml);
  }

  public function testToStringMagic()
  {
    $dom = new HtmlDomParser();
    $dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
    self::assertEquals('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br> :)</p></div>', (string) $dom);
  }

  public function testGetMagic()
  {
    $dom = new HtmlDomParser();
    $dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
    self::assertEquals('<p>Hey bro, <a href="google.com">click here</a><br> :)</p>', $dom->innerHtml);
  }

  public function testGetElementById()
  {
    $dom = new HtmlDomParser();
    $dom->load('<div class="all"><p>Hey bro, <a href="google.com" id="78">click here</a></div><br />');
    self::assertEquals('<a href="google.com" id="78">click here</a>', $dom->getElementById('78')->outerHtml);
  }

  public function testGetElementsByTag()
  {
    $dom = new HtmlDomParser();
    $dom->load('<div class="all"><p>Hey bro, <a href="google.com" id="78">click here</a></div><br />');
    $elm = $dom->getElementsByTagName('p');
    self::assertEquals(
        '<p>Hey bro, <a href="google.com" id="78">click here</a></p>',
        $elm[0]->outerHtml
    );
  }

  public function testGetElementsByClass()
  {
    $dom = new HtmlDomParser();
    $dom->load('<div class="all"><p>Hey bro, <a href="google.com" id="78">click here</a></div><br />');
    $elm = $dom->find('.all');
    self::assertEquals(
        '<p>Hey bro, <a href="google.com" id="78">click here</a></p>',
        $elm[0]->innerHtml
    );
  }

  public function testEnforceEncoding()
  {
    $dom = new HtmlDomParser();
    $dom->load('tests/files/horrible.html');

    self::assertNotEquals('<input type="submit" tabindex="0" name="submit" value="Информации" />', $dom->find('table input', 1)->outerHtml);
  }

  public function testScriptCleanerScriptTag()
  {
    $dom = new HtmlDomParser();
    $dom->load('
        <p>.....</p>
        <script>
        Some code ... 
        document.write("<script src=\'some script\'><\/script>") 
        Some code ... 
        </script>
        <p>....</p>');
    $elm = $dom->getElementsByTagName('p');
    self::assertEquals('....', $elm[1]->innerHtml);
  }

  public function testBeforeClosingTag()
  {
    $dom = new HtmlDomParser();
    $dom->load("<div class=\"stream-container \"  > <div class=\"stream-item js-new-items-bar-container\"> </div> <div class=\"stream\">");
    self::assertEquals("<div class=\"stream-container \"> <div class=\"stream-item js-new-items-bar-container\"> </div> <div class=\"stream\"></div></div>", (string) $dom);
  }

  public function testCodeTag()
  {
    $dom = new HtmlDomParser();
    $dom->load('<strong>hello</strong><code class="language-php">$foo = "bar";</code>');
    self::assertEquals('<strong>hello</strong><code class="language-php">$foo = "bar";</code>', (string) $dom);
  }

  public function testDeleteNode()
  {
    $dom = new HtmlDomParser();
    $dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
    $a   = $dom->find('a');
    $a[0]->outertext = '';
    unset($a);
    self::assertEquals('<div class="all"><p>Hey bro, <br> :)</p></div>', (string) $dom);
  }
}
