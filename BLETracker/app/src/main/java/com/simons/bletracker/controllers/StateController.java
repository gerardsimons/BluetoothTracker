package com.simons.bletracker.controllers;

/**
 * Created by gerard on 24/07/15.
 *
 * The responsibility of this class is to manage the different states the application can find itself in
 * Any changes to the state should be delegated to this class, upon which this controller may decide
 * what the new state is according to some action
 */
public class StateController {

    public enum State {
        IDLE,
        CASE_SCANNED,
        READY_FOR_DEPARTURE,
        EN_ROUTE,
        ARRIVED,
        RETURNED
    }

    public enum Action {
        SCAN_CASE,
        SCAN_LABEL,
        DEPART,
        ARRIVE,
        RETURN
    }

    private State state = State.IDLE;
    private static StateController instance;

    private StateController() {

    }

    public static StateController GetInstance() {
        if(instance == null) {
            instance = new StateController();
        }
        return instance;
    }

    public State doAction(Action action) throws IllegalStateException {
        switch(state) {
            case IDLE:
                if(action == Action.SCAN_CASE) {
                    state = State.CASE_SCANNED;
                }
                else throw new IllegalStateException();
                break;
            case CASE_SCANNED:
                if(action == Action.SCAN_LABEL) {
                    state = State.READY_FOR_DEPARTURE;
                }
                else throw new IllegalStateException();
                break;
            case READY_FOR_DEPARTURE: //It is allowed to depart or to scan another case-tag pair
                if(action == Action.DEPART) {
                    state = State.EN_ROUTE;
                }
                else if(action == Action.SCAN_CASE) {
                    state = State.CASE_SCANNED;
                }
                else throw new IllegalStateException();
                break;
            case EN_ROUTE: //It is allowed to arrive or add a checkpoint
                break;
            case ARRIVED:
                break;
            case RETURNED:
                break;
        }

        return state;
    }

    public State getState() {
        return state;
    }
}
