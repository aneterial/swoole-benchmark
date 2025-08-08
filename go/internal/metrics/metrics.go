package metrics

import (
	"fmt"
	"runtime"
	"slices"
	"sync"
)

type Type string

const (
	MemoryStart   Type = "start"
	MemoryEnd     Type = "end"
	MemoryProcess Type = "process"

	Limit = 500
)

type MetricsUnit struct {
	Data  []uint64
	Mutex sync.RWMutex
}

type Metrics struct {
	Units map[Type]*MetricsUnit
}

func New() *Metrics {
	units := [...]Type{MemoryStart, MemoryEnd, MemoryProcess}
	unitsMap := make(map[Type]*MetricsUnit, len(units))
	for _, unit := range units {
		unitsMap[unit] = &MetricsUnit{
			Data: make([]uint64, 0, Limit+10),
		}
	}

	return &Metrics{
		Units: unitsMap,
	}
}

func (m *Metrics) Save(unitType Type, value uint64) {
	go func() {
		unit, ok := m.Units[unitType]
		if !ok {
			return
		}

		unit.Mutex.Lock()
		defer unit.Mutex.Unlock()

		unit.Data = append(unit.Data, value)
		if len(unit.Data) > Limit {
			unit.Data = unit.Data[1:]
		}
	}()
}

func (m *Metrics) GetStats(unitType Type) map[string]any {
	unit, ok := m.Units[unitType]
	if !ok || len(unit.Data) == 0 {
		return map[string]any{
			"raw": map[string]uint64{
				"max": 0,
				"min": 0,
				"avg": 0,
				"p95": 0,
			},
			"formatted": map[string]string{
				"max": formatBytes(0, 2),
				"min": formatBytes(0, 2),
				"avg": formatBytes(0, 2),
				"p95": formatBytes(0, 2),
			},
			"values": []uint64{},
			"count":  0,
		}
	}

	sorted := make([]uint64, len(unit.Data))
	copy(sorted, unit.Data)
	slices.Sort(sorted)

	count := len(sorted)
	max := sorted[count-1]
	min := sorted[0]

	var sum uint64
	for _, v := range sorted {
		sum += v
	}
	avg := sum / uint64(count)

	p95Index := int(float64(count) * 0.95)
	if p95Index >= count {
		p95Index = count - 1
	}
	p95 := sorted[p95Index]

	lastValues := sorted
	if len(sorted) > 50 {
		lastValues = sorted[len(sorted)-50:]
	}

	return map[string]any{
		"raw": map[string]uint64{
			"max": max,
			"min": min,
			"avg": avg,
			"p95": p95,
		},
		"formatted": map[string]string{
			"max": formatBytes(max, 2),
			"min": formatBytes(min, 2),
			"avg": formatBytes(avg, 2),
			"p95": formatBytes(p95, 2),
		},
		"values": lastValues,
		"count":  count,
	}
}

func GetMemoryUsage() uint64 {
	var m runtime.MemStats
	runtime.ReadMemStats(&m)
	return m.Alloc
}

func formatBytes(bytes uint64, precision int) string {
	units := []string{"B", "KB", "MB", "GB", "TB"}

	value := float64(bytes)
	i := 0
	for value > 1024 && i < len(units)-1 {
		value /= 1024
		i++
	}

	return fmt.Sprintf("%.*f %s", precision, value, units[i])
}
