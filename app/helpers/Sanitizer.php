<?php
/**
 * Helper de Sanitização e Validação de Dados
 * Previne ataques XSS, dados maliciosos e inconsistências
 */

class Sanitizer {
    public static function texto($valor, $limite = null) {
        if ($valor === null) return '';
        $limpo = trim((string)$valor);
        $limpo = strip_tags($limpo);
        $limpo = htmlspecialchars($limpo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        if ($limite !== null && mb_strlen($limpo) > $limite) {
            $limpo = mb_substr($limpo, 0, $limite);
        }
        return $limpo;
    }

    public static function email($valor) {
        $email = filter_var(trim((string)$valor), FILTER_SANITIZE_EMAIL);
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? strtolower($email) : false;
    }

    public static function inteiro($valor, $padrao = 0) {
        $filtrado = filter_var($valor, FILTER_VALIDATE_INT);
        return ($filtrado !== false) ? (int)$filtrado : $padrao;
    }

    public static function data($valor) {
        if (empty($valor)) return null;
        $d = DateTime::createFromFormat('Y-m-d', trim($valor));
        return ($d && $d->format('Y-m-d') === trim($valor)) ? trim($valor) : null;
    }

    public static function enum($valor, array $opcoesPermitidas, $padrao) {
        $valor = trim(strtolower((string)$valor));
        return in_array($valor, $opcoesPermitidas, true) ? $valor : $padrao;
    }
}
