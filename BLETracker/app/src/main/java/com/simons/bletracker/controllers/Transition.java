package com.simons.bletracker.controllers;

/**
 * Created by gerard on 02/08/15.
 */
public class Transition {

    public final State fromState;
    public final State toState;
    public final Action action;

    public Transition(State fromState, State toState, Action action) {
        this.action = action;
        this.fromState = fromState;
        this.toState = toState;
    }
}
