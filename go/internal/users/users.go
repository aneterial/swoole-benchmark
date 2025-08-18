package users

import (
	"context"
	"log"
	"time"

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

func (u *Users) GetUsers(ctx context.Context, searchName string) ([]User, int64) {
	usersChan := make(chan []User)
	totalChan := make(chan int64)

	go func() {
		users := []User{}
		defer func() { usersChan <- users }()

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
	}()

	go func() {
		var total int64
		if err := u.db.QueryRow(ctx, "SELECT COUNT(*) FROM users WHERE name LIKE $1", "%"+searchName+"%").Scan(&total); err != nil {
			log.Printf("Failed to query users total: %v", err)
		}
		totalChan <- total
	}()

	return <-usersChan, <-totalChan
}
