<?php

namespace App\Libraries;

use CodeIgniter\I18n\Time;
use InvalidArgumentException;

class CalendarService
{

    /** @var array meses */
    private static array $months = [
        1  => 'Janeiro',
        2  => 'Fevereiro',
        3  => 'Março',
        4  => 'Abril',
        5  => 'Maio',
        6  => 'Junho',
        7  => 'Julho',
        8  => 'Agosto',
        9  => 'Setembro',
        10 => 'Outubro',
        11 => 'Novembro',
        12 => 'Dezembro'
    ];


    /**
     * Renderiza um dropdown dos meses para serem escolhidos no agendamento
     *
     * @return string
     */
    // Método responsável por renderizar o dropdown de seleção de meses
    public function renderMonths(): string
    {
        // Obtém a data e hora atual
        $today          = Time::now();
        // Obtém o ano atual
        $currentYear    = $today->getYear();
        // Obtém o mês atual
        $currentMonth   = $today->getMonth();

        // Inicializa um array vazio para armazenar as opções do dropdown
        $options = [];
        // Adiciona uma opção padrão para "Escolha"
        $options[null] = '--- Escolha ---';

        // Loop através dos meses do array $months
        foreach (self::$months as $key => $month) {

            // Verifica se o mês atual é maior que o valor de '$key'
            if ($currentMonth > $key) {

                // Se o mês atual for maior que o valor de '$key',
                // continuamos para o próximo mês no loop,
                // pois não queremos exibir meses passados
                continue;
            }

            // Adiciona uma opção para o dropdown com o nome do mês e o ano atual
            $options[$key] = "{$month} / {$currentYear}";
        }

        // Retorna o HTML do dropdown de seleção de meses
        return form_dropdown(data: 'month', options: $options, selected: [], extra: ['id' => 'month', 'class' => 'form-select']);
    }


    /**
     * Renderiza os dias para o mês informado para serem escolhidos no front.
     *
     * @param integer $month
     * @throws Exception
     * @return string
     */
    public function generate(int $month): string
    {

        try {

            // tempo atual
            $now = Time::now();

            // mês atual
            $currentMonth = (int) $now->getMonth();

            // ano atual
            $year = (int) $now->getYear();

            // vamos garantir que apenas meses válidos sejam aceitos,
            // ou seja, tem que ser maior que o mês atual e que sejam válidos
            if ($month < $currentMonth || !in_array($month, array_keys(self::$months))) {

                throw new InvalidArgumentException("O mês {$month} não é um mês válido para gerar o calendário");
            }

            // criamos um novo objeto para termos acesso ao dia da semana do primeiro dia do mês
            $firstDayObject = $now::create(year: $year, month: $month, day: 1);

            // obtém a quantidade de dias do mês
            $daysOfMonth = $firstDayObject->format('t');

            // obtém a representação numérica do dia da semana. 0 (domingo) até 6 (sabádo)
            $startDay = (int) $firstDayObject->format('w'); // minúsculo

            // abertura da div que comporta o calendário
            $calendar = '<div class="table-responsive">';

            // abertura da tabela
            $calendar .= '<table class="table table-sm table-borderless">';

            // dias da semana (primeira linha da tabela)
            $calendar .= '<tr class="text-center">
                            <td>Dom</td>
                            <td>Seg</td>
                            <td>Ter</td>
                            <td>Qua</td>
                            <td>Qui</td>
                            <td>Sex</td>
                            <td>Sáb</td>
                           </tr>
                        ';


            // enquanto o dia de início for maior que zero, adiciono as células vazias através do for, 
            // até que encontremos o dia inicial da semana
            if ($startDay > 0) {

                for ($i = 0; $i < $startDay; $i++) {

                    $calendar .= '<td>&nbsp;</td>';
                }
            }


            // nesse ponto podemos popular o calendário
            for ($day = 1; $day <= $daysOfMonth; $day++) {

                /**
                 * @todo renderizar botão com o dia
                 */
                $btnDay = $this->renderDayButton(
                    day: $day,
                    month: $month,
                    isWeekend: $this->isWeekend(year: $year, month: $month, day: $day)
                );

                $calendar .= "<td>{$btnDay}</td>";

                // vamos incrementar o dia de início
                $startDay++;

                // se $startDay for igual a 7 (domingo), adicionamos uma nova linha na tabela
                if ($startDay === 7) {

                    // reinicio o startDay em zero
                    $startDay = 0;


                    // se o dia corrente for menor que $daysOfMonth, então realizamos a abertura da <tr> (nova linha)
                    if ($day < $daysOfMonth) {

                        $calendar .= '<tr>';
                    }
                }
            } // fim do for


            // agora preenchemos as células restantes com espaço
            if ($startDay > 0) {

                for ($i = $startDay; $i < 7; $i++) {

                    $calendar .= '<td>&nbsp;</td>';
                }

                // e fechamos a linha
                $calendar .= '</tr>';
            }

            // fechamos a tebela
            $calendar .= '</table>';

            // fechamos a div table-responsive
            $calendar .= '</div>';

            // finalmente retornamos o calendário com dias para o mês desejado
            return $calendar;
        } catch (\Throwable $th) {

            log_message('error', '[ERROR] {exception}', ['exception' => $th]);

            return "Não foi possível gerar o calendário para o mês informado";
        }
    }


    /**
     * Verifica se a ata informada é um final de semana.
     *
     * @param integer $year
     * @param integer $month
     * @param integer $day
     * @return boolean
     */
    private function isWeekend(int $year, int $month, int $day): bool
    {
        // vamos obter o primeiro dia do mês informado no formato unix timestamp
        $timeCreated = Time::create(year: $year, month: $month, day: $day);

        // obtém a representação numérica do dia da semana. 0 (domingo) até 6 (sabádo)
        $dayOfWeek = (int) $timeCreated->format('w'); // minúsculo


        // 0 => domindo ou 6 => sábado
        return ($dayOfWeek === 0 || $dayOfWeek === 6);
    }


    /**
     * Renderiza o botão HTML para click no front
     *
     * @param integer $day
     * @param integer $month
     * @param boolean $isWeekend
     * @return string
     */
    private function renderDayButton(int $day, int $month, bool $isWeekend = false): string
    {

        // atributos padrão para o botão
        $attributes = [
            'type'  => 'button',
            'class' => 'btn btn-primary btn-calendar-day',
        ];


        // data atual
        $now          = Time::now();
        $currentDay   = (int) $now->getDay();
        $currentMonth = (int) $now->getMonth();

        // se o dia for menor que o dia atual e o mês for igual ao mês corrente
        // então desabilitamos o botão
        if ($day < $currentDay && $month === $currentMonth || $isWeekend) {

            $attributes['disabled'] = true;
        } else {

            $attributes['class'] = "chosenDay {$attributes['class']}"; // usados no front
            $attributes['data-day'] = $day; // usados no front
        }


        return form_button(data: $attributes, content: "{$day}");
    }
}
