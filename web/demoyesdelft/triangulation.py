from numpy import amin, amax, round, array, size, argmin, linspace, reshape, zeros, vstack, append, sqrt, absolute, sum
from math import log10


def triangulate(data):
    distancefrac = 0.001
    margin = 10
    sig = 2
    f = 3

    xmin = amin(data[:, 0])
    ymin = amin(data[:, 1])
    xmax = amax(data[:, 0])
    ymax = amax(data[:, 1])

    maxdis = max(xmax - xmin, ymax - ymin)
    gridstep = distancefrac * maxdis
    roundto = pow(10, round(log10(gridstep)))

    bounds = array([xmin - margin, ymin - margin, xmax + margin, ymax + margin])

    absb = 10 ** (data[:, 2:5] / 10)
    reldis = round(pow(10, sig) / absb ** (1. / f)) / pow(10, sig)
    data[:, 2:5] = reldis

    minima = find_min(data, gridstep, roundto, bounds)
    if minima != None:
        if size(minima, 0):
            minimum = minima[argmin(minima[:, 2]), :2]
        else:
            minimum = minima[0, :2]

        return minimum
    else:
        return


def find_min(data, gridstep, roundto, bounds, minval=False):
    steps = 10

    stepx = (bounds[2] - bounds[0]) / (steps - 1.)
    stepy = (bounds[3] - bounds[1]) / (steps - 1.)

    if stepx < gridstep or stepy < gridstep:
        returnval = True
    else:
        returnval = False

    points = reshape([array([x, y]) for x in linspace(bounds[0], bounds[2], steps)
                      for y in linspace(bounds[1], bounds[3], steps)], (steps ** 2, 2))
    values = reshape(get_values(data, points), (steps, steps))

    minima = zeros((0, 3))
    excludebounds = zeros((0, 4))
    buf = zeros((0, 5))
    for i in range(steps):
        for j in range(steps):
            xmin = False
            ymin = False
            xmax = False
            ymax = False

            val = values[i, j]
            if val > minval and minval:
                continue

            surrounding = zeros((0, 1))
            for m in range(-1, 2):
                i_m = i + m
                if 1 <= i_m < steps:
                    x = bounds[0] + i_m * stepx
                    if x < xmin or not xmin:
                        xmin = x
                    if x > xmax or not xmax:
                        xmax = x
                else:
                    continue

                for n in range(-1, 2):
                    j_n = j + n
                    if 1 <= j_n < steps:
                        y = bounds[1] + j_n * stepy
                        if y < ymin or not ymin:
                            ymin = y
                        if y > ymax or not ymax:
                            ymax = y
                    else:
                        continue

                    if m != 0 or n != 0:
                        surrounding = append(surrounding, values[i_m, j_n])

            vmin = amin(surrounding)

            if val <= vmin:
                if not returnval:
                    nextbounds = get_bounds(array([xmin, ymin, xmax, ymax]), excludebounds, roundto)
                    if size(nextbounds) > 1:
                        excludebounds = vstack([excludebounds, nextbounds])
                        minval = val
                        buf = vstack([buf, append(nextbounds, minval)])
                else:
                    minval = val
                    x = bounds[0] + i * stepx
                    y = bounds[1] + j * stepy
                    minima = vstack((minima, array([x, y, val])))

    if not returnval:
        for i in range(size(buf, 0)):
            searchbounds = buf[i, 0:4]
            searchminval = buf[i, 4]

            extraminima = find_min(data, gridstep, roundto, searchbounds, searchminval)
            if extraminima != None:
                for j in range(size(extraminima, 0)):
                    extraminval = extraminima[j, 2]
                    if extraminval < minval:
                        minval = extraminval
                    minima = vstack((minima, extraminima[j, :]))
                return minima
    else:
        return minima


def get_values(data, points):
    data_size = size(data, 0)
    points_size = size(points, 0)

    values = zeros(points_size)

    for i in range(points_size):
        ds = sqrt((data[:, 0] - points[i, 0])**2 + (data[:, 1] - points[i, 1])**2)

        values[i] = sum(
            [
                amax(
                    absolute(
                        data[k, 2:5] * ds[j] / data[j, 2] - ds[k]
                    )
                ) ** 1.1 / (data_size**2 - data_size)
                for j in range(data_size)
                for k in range(data_size)
                if j != k
            ]
        )

    return values


def get_bounds(bounds, exclude, roundto):
    newbounds = bounds

    allgood = False
    while not allgood:
        allgood = True

        xmin, ymin, xmax, ymax = round(newbounds / roundto) * roundto

        if ymin >= ymax or xmin >= xmax:
            return 0

        for i in range(size(exclude, 0)):
            xemin, yemin, xemax, yemax = round(exclude[i, :] / roundto) * roundto

            if (xmin < xemin or xmax > xemax) and (ymin < yemin or ymax > yemax):
                continue

            if xmin >= xemin and ymax > yemax:
                if ymin < yemax < ymax:
                    allgood = False
                    newbounds[1] = exclude[i, 3]
                if ymax > yemin > ymin:
                    allgood = False
                    newbounds[3] = exclude[i, 1]
                if ymax <= yemax and ymin >= yemin:
                    return 0
                break
            elif ymin >= yemin and ymax <= yemax:
                if xmin < xemax < xmax:
                    allgood = False
                    newbounds[0] = exclude[i, 2]
                if xmax > xemin > xmin:
                    allgood = False
                    newbounds[2] = exclude[i, 0]
                if xmax <= xemax and xmin >= xemin:
                    return 0
                break

    return newbounds