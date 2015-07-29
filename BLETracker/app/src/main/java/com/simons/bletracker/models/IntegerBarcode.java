package com.simons.bletracker.models;

/**
 * Created by gerard on 29/07/15.
 */
public class IntegerBarcode {

    private int[] components;

    public IntegerBarcode(String barcode, int[] componentIndices) {

        components = new int[componentIndices.length];
        int startI = 0;
        for(int i = 0 ; i < componentIndices.length ; ++i) {
            int endI = startI + componentIndices[i];
            components[i] = Integer.parseInt(removeLeadingZeros(barcode.substring(startI,endI)));
            startI = endI;
        }
    }

    //TODO: Move to utilities class
    private String removeLeadingZeros(String in) {
        return in.replaceFirst("^0+(?!$)", "");
    }

    public int getComponent(int ID) {
        return components[ID];
    }
}
