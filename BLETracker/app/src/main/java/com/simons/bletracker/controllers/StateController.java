package com.simons.bletracker.controllers;

import android.util.Log;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by gerard on 24/07/15.
 *
 * The responsibility of this class is to manage the different states the application can find itself in
 * Any changes to the state should be delegated to this class, upon which this controller may decide
 * what the new state is accordingly
 */
public class StateController {

    private static final String TAG = "StateController";

    private static StateController instance;

    private State state = State.IDLE;
    private List<OnStateChangedListener> listeners;

    public interface OnStateChangedListener {
        public void OnStateTransitioned(Transition transition);
    }

    private StateController() {
        listeners = new ArrayList<>();
    }

    private void notifyListeners(Transition transition) {
        for(OnStateChangedListener listener : listeners) {
            listener.OnStateTransitioned(transition);
        }
    }

    public static StateController GetInstance() {
        if(instance == null) {
            instance = new StateController();
        }
        return instance;
    }

    public State doAction(Action action) throws IllegalStateException {

        State oldState = state;

        switch(state) {
            case IDLE:
                if(action == Action.SCAN_CASE) {
                    state = State.READY_FOR_DEPARTURE;
                }
                else throw new IllegalStateException();
                break;
            case READY_FOR_DEPARTURE: //It is allowed to depart or to scan another case-tag pair
                if(action == Action.DEPART) {
                    state = State.EN_ROUTE;
                }
                else if(action == Action.SCAN_CASE) {
                    //Stay in the same state
                    state = State.READY_FOR_DEPARTURE;
                }
                else throw new IllegalStateException();
                break;
            case EN_ROUTE: //It is allowed to arrive or add a checkpoint
                if(action == Action.FINISHED) {
                    state = State.ALL_DELIVERED;
                }
                break;
            case ALL_DELIVERED:
                if(action == Action.RETURN) {
                    state = State.RETURNED;
                }
                break;
            case RETURNED:
                if(action == Action.RESET)
                    state = State.IDLE;
                break;
        }

        Log.d(TAG, "New state after action " + action + " = " + state);
        notifyListeners(new Transition(oldState,state,action));

        return state;
    }

    public void registerListener(OnStateChangedListener listener) {
        listeners.add(listener);
    }

    public State getState() {
        return state;
    }
}
