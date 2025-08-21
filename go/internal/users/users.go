package users

import (
	"context"
	"log"
	"sync"
	"time"

	"github.com/google/uuid"
	"github.com/jackc/pgx/v5"
	"github.com/jackc/pgx/v5/pgxpool"
)

type User struct {
	Id        int       `json:"id"`
	Age       int       `json:"age"`
	Name      string    `json:"name"`
	Email     string    `json:"email"`
	CreatedAt time.Time `json:"created_at"`
	UpdatedAt time.Time `json:"updated_at"`
}

type Users struct {
	db *pgxpool.Pool
}

func New(db *pgxpool.Pool) *Users {
	return &Users{
		db: db,
	}
}

func (u *Users) GetUsers(ctx context.Context, searchName string) (map[string]User, int64) {
	var wg sync.WaitGroup
	var users []User
	var total int64

	uuids := make([]uuid.UUID, 0, 1000)

	wg.Go(func() {
		rows, err := u.db.Query(ctx, "SELECT id, name, email, age, created_at, updated_at FROM users WHERE name LIKE $1 LIMIT 100", "%"+searchName+"%")
		if err != nil {
			log.Printf("Failed to query users: %v", err)
			return
		}
		defer rows.Close()

		users, err = pgx.CollectRows(rows, pgx.RowToStructByName[User])
		if err != nil {
			log.Printf("Failed to scan users: %v", err)
		}
	})
	wg.Go(func() {
		if err := u.db.QueryRow(ctx, "SELECT COUNT(*) FROM users WHERE name LIKE $1", "%"+searchName+"%").Scan(&total); err != nil {
			log.Printf("Failed to query users total: %v", err)
		}
	})
	wg.Go(func() {
		for range cap(uuids) {
			uuid, _ := uuid.NewV7()
			uuids = append(uuids, uuid)
		}
	})

	wg.Wait()

	result := make(map[string]User)
	for i, user := range users {
		result[uuids[i].String()] = user
	}

	return result, total
}
