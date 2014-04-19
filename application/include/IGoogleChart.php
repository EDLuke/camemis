<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author DELL
 */
interface IChart {

    const TOP = 1;
    const RIGHT = 2;
    const BOTTOM = 3;
    const LEFT = 4;
    const CENTER = 5;

    public function renderJs();
    public function show();
    public function getPosition();
}

?>
