<?php

namespace Wappointment\Messages\Templates;

class Order
{
    public static function table($rows)
    {
        return '<table align="center" class="callout">
        <tbody>
          <tr>
            <th class="callout-inner secondary">' . static::generateRows($rows) . '</th>
          </tr>
        </tbody>
      </table>';
    }

    public static function cell($content)
    {
        return '<table>
                    <tbody>
                    <tr>
                        <th>' . $content . '</th>
                    </tr>
                    </tbody>
                </table>';
    }


    protected static function generateRows($rows)
    {
        $html = '';
        foreach ($rows as $row) {
            $html .= static::row($row);
        }
        return $html;
    }

    public static function row($cells)
    {
        $html = '<table class="row">
        <tbody>
          <tr>';
        foreach ($cells as $cell) {
            $html .= '<th class="small-12 large-4 columns first">' . static::cell($cell) . '</th>';
        }
        $html .= '</tr>
        </tbody>
      </table>';
        return $html;
    }
}
