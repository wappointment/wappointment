<?php

namespace Wappointment\Messages\Templates;

class Order
{
  public static function addStyle($style)
  {
    return $style . ' .ordercell{
      padding-left: 10px;
      color:#756f6f;
    }
    .bold .ordercell{
      font-weight: bold;
    }
    .linesep{
      border-bottom: 4px solid #cccccc;
      padding-bottom:6px;
      margin-bottom:6px;
    }
    .lineb{
      border-bottom: 1px solid #cccccc;
    }
    .linet{
      border-top: 1px solid #cccccc;
    }
    .small .ordercell{
      font-size: 12px;
    }';
  }
  public static function table($rows)
  {
    add_filter('wappointment_style_email', [static::class, 'addStyle']);
    return '<table align="center" class="callout">
        <tbody>
          <tr>
            <th class="callout-inner secondary radius">' . static::generateRows($rows) . '</th>
          </tr>
        </tbody>
      </table>';
  }

  public static function cell($content)
  {
    return '<table>
                    <tbody>
                    <tr>
                        <th class="ordercell">' . $content . '</th>
                    </tr>
                    </tbody>
                </table>';
  }


  protected static function generateRows($rows)
  {
    $html = '';
    foreach ($rows as $key => $row) {
      $rowContent = empty($row['cells']) ? $row : $row['cells'];
      $rowClass = empty($row['class']) ? '' : $row['class'];
      $html .= static::row($rowContent, $key, $rowClass, (count($rows) == $key + 1));
    }
    return $html;
  }

  public static function row($cells, $rowNumber, $class, $last = false)
  {
    $class .= ' row' . ($rowNumber < 1 ? ' rowheader' : '');
    $classCell = ($rowNumber < 1 ? ' first' : ($last ? ' last' : ' middle'));
    $html = '<table class="' . $class . '">
        <tbody>
          <tr>';
    foreach ($cells as $cell) {
      $html .= '<th class="small-12 large-4 columns ' . $classCell . '">' . static::cell($cell) . '</th>';
    }
    $html .= '</tr>
        </tbody>
      </table>';
    return $html;
  }
}
